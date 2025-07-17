FROM php:8.2-fpm-alpine

# Ustaw katalog roboczy
WORKDIR /var/www/html

# Instalacja zależności systemowych
RUN apk update && apk add --no-cache \
    zip \
    unzip \
    libzip \
    libzip-dev \
    libpng-dev \
    oniguruma-dev \
    pkgconfig \
    cmake \
    linux-headers \
    postgresql-dev \
    curl \
    bash \
    git \
    redis \
    supervisor \
    nginx

# Rozszerzenia PHP (install in separate steps to isolate issues)
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql exif pcntl bcmath

# Configure and install zip extension manually
RUN docker-php-ext-configure zip || \
    LIBZIP_CFLAGS="-I/usr/include" LIBZIP_LIBS="-L/usr/lib -lzip" docker-php-ext-configure zip
RUN docker-php-ext-install zip

# Configure and install mbstring with manual oniguruma flags
RUN docker-php-ext-configure mbstring --disable-mbregex || \
    ONIG_CFLAGS="-I/usr/include" ONIG_LIBS="-L/usr/lib -lonig" docker-php-ext-configure mbstring
RUN docker-php-ext-install mbstring

# Instalacja Redis extension
RUN apk add --no-cache --virtual .build-deps \
    autoconf \
    build-base \
    libtool \
    pkgconfig \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Instalacja Composera
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Skopiuj pliki composer dla cache'owania warstw
COPY composer.json composer.lock ./

# Instalacja zależności PHP (przed kopiowaniem kodu dla lepszego cache'owania)
RUN composer install --no-interaction --prefer-dist --no-scripts --no-dev --optimize-autoloader

# Skopiuj pliki projektu
COPY . .

# Utwórz wymagane katalogi Laravel
RUN mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/storage/framework/cache \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/bootstrap/cache

# Prawa dostępu
RUN chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Konfiguracja Nginx
RUN mkdir -p /var/log/nginx /var/cache/nginx /var/run
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# Konfiguracja Supervisor
RUN mkdir -p /etc/supervisor/conf.d
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Skrypt startowy
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Zmienne środowiskowe
ENV APP_ROLE=web

EXPOSE 8080 9000

CMD ["/usr/local/bin/start.sh"]
