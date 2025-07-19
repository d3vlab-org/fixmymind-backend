#!/bin/bash
# Skrypt startowy obsługujący php-fpm oraz artisan serve w zależności od APP_ROLE

# Utwórz katalogi dla logów
mkdir -p /var/log/supervisor

# Przygotuj aplikację Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ustaw odpowiednie uprawnienia
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache

# W zależności od roli uruchom odpowiednią usługę
case "$APP_ROLE" in
    "web")
        exec php artisan serve --host=0.0.0.0 --port=8080
        ;;
    "dev")
        exec php artisan serve --host=0.0.0.0 --port=8080
        ;;
    "fpm")
        exec php-fpm
        ;;
    "test")
        exec php artisan test
        ;;
    "worker")
        exec php artisan queue:work --sleep=3 --tries=3 --max-time=3600
        ;;
    "all"|*)
        # Domyślnie uruchamiamy cały zestaw procesów przez Supervisora
        exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
        ;;
esac
