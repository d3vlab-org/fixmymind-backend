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

# Generuj konfigurację supervisor na podstawie APP_ROLE
cat > /tmp/supervisord.conf << EOF
[supervisord]
nodaemon=true
user=root
logfile=/var/log/supervisor/supervisord.log
pidfile=/var/run/supervisord.pid

[program:redis-server]
command=redis-server
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/redis.stderr.log
stdout_logfile=/var/log/supervisor/redis.stdout.log
user=root

EOF

# Sprawdź APP_ROLE i dodaj odpowiednie programy
case "$APP_ROLE" in
    "web")
        echo "Configuring for web server (Nginx + PHP-FPM)..."
        cat >> /tmp/supervisord.conf << EOF
[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/nginx.stderr.log
stdout_logfile=/var/log/supervisor/nginx.stdout.log
user=root

[program:php-fpm]
command=php-fpm
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/php-fpm.stderr.log
stdout_logfile=/var/log/supervisor/php-fpm.stdout.log
user=root
EOF
        ;;
    "dev"|"test")
        echo "Configuring for development server (artisan serve)..."
        cat >> /tmp/supervisord.conf << EOF
[program:artisan-serve]
command=php artisan serve --host=0.0.0.0 --port=8080
directory=/var/www/html
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/artisan-serve.stderr.log
stdout_logfile=/var/log/supervisor/artisan-serve.stdout.log
user=www-data
EOF
        ;;
    "worker")
        echo "Configuring for queue worker..."
        cat >> /tmp/supervisord.conf << EOF
[program:queue-worker]
command=php artisan queue:work --sleep=3 --tries=3 --max-time=3600
directory=/var/www/html
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/queue-worker.stderr.log
stdout_logfile=/var/log/supervisor/queue-worker.stdout.log
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile_maxbytes=100MB
EOF
        ;;
    "all")
        echo "Configuring for all services (nginx + web + worker)..."
        cat >> /tmp/supervisord.conf << EOF
[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/nginx.stderr.log
stdout_logfile=/var/log/supervisor/nginx.stdout.log
user=root

[program:php-fpm]
command=php-fpm
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/php-fpm.stderr.log
stdout_logfile=/var/log/supervisor/php-fpm.stdout.log
user=root

[program:queue-worker]
command=php artisan queue:work --sleep=3 --tries=3 --max-time=3600
directory=/var/www/html
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/queue-worker.stderr.log
stdout_logfile=/var/log/supervisor/queue-worker.stdout.log
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile_maxbytes=100MB
EOF
        ;;
    *)
        echo "Unknown APP_ROLE: $APP_ROLE. Defaulting to web server..."
        cat >> /tmp/supervisord.conf << EOF
[program:php-fpm]
command=php-fpm
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/php-fpm.stderr.log
stdout_logfile=/var/log/supervisor/php-fpm.stdout.log
user=root
EOF
        ;;
esac

# Uruchom Supervisor z wygenerowaną konfiguracją
exec /usr/bin/supervisord -n -c /tmp/supervisord.conf
