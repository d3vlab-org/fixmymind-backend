#!/bin/bash

# Utwórz katalogi dla logów
mkdir -p /var/log/supervisor

# Przygotuj aplikację Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ustaw odpowiednie uprawnienia
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache


# Uruchom Supervisor z wygenerowaną konfiguracją
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
