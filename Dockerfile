FROM php:8.3-fpm-alpine

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
    git

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

# Instalacja Composera
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Ustaw katalog roboczy
WORKDIR /var/www/html

# Skopiuj pliki projektu
COPY . .

# Instalacja zależności PHP
RUN composer install --no-interaction --prefer-dist --no-scripts

# Prawa dostępu
RUN chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]