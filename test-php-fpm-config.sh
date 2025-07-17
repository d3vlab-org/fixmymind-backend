#!/bin/bash

echo "Testing PHP-FPM configuration for fly.io deployment..."

# Check if PHP-FPM pool configuration exists
if [ -f "docker/www.conf" ]; then
    echo "✓ PHP-FPM pool configuration exists"

    # Check if it's configured to listen on port 8080
    if grep -q "listen = 0.0.0.0:8080" docker/www.conf; then
        echo "✓ PHP-FPM configured to listen on port 8080"
    else
        echo "✗ PHP-FPM not configured for port 8080"
    fi
else
    echo "✗ PHP-FPM pool configuration missing"
fi

# Check supervisord configuration
if [ -f "docker/supervisord.conf" ]; then
    echo "✓ Supervisord configuration exists"

    # Check if PHP-FPM is enabled
    if grep -A 5 "\[program:php-fpm\]" docker/supervisord.conf | grep -q "autostart=true"; then
        echo "✓ PHP-FPM autostart enabled"
    else
        echo "✗ PHP-FPM autostart not enabled"
    fi

    # Check if artisan-serve is removed
    if grep -q "\[program:artisan-serve\]" docker/supervisord.conf; then
        echo "✗ artisan-serve still present in configuration"
    else
        echo "✓ artisan-serve removed from configuration"
    fi

    # Check if queue-worker is enabled
    if grep -A 5 "\[program:queue-worker\]" docker/supervisord.conf | grep -q "autostart=true"; then
        echo "✓ Queue worker autostart enabled"
    else
        echo "✗ Queue worker autostart not enabled"
    fi

    # Check if redis is enabled
    if grep -A 5 "\[program:redis-server\]" docker/supervisord.conf | grep -q "autostart=true"; then
        echo "✓ Redis server autostart enabled"
    else
        echo "✗ Redis server autostart not enabled"
    fi
else
    echo "✗ Supervisord configuration missing"
fi

# Check Dockerfile
if [ -f "Dockerfile" ]; then
    echo "✓ Dockerfile exists"

    # Check if nginx is removed
    if grep -q "nginx" Dockerfile; then
        echo "✗ Nginx still present in Dockerfile"
    else
        echo "✓ Nginx removed from Dockerfile"
    fi

    # Check if PHP-FPM config is copied
    if grep -q "COPY docker/www.conf /usr/local/etc/php-fpm.d/www.conf" Dockerfile; then
        echo "✓ PHP-FPM configuration copied in Dockerfile"
    else
        echo "✗ PHP-FPM configuration not copied in Dockerfile"
    fi

    # Check if only port 8080 is exposed
    if grep -q "EXPOSE 8080$" Dockerfile; then
        echo "✓ Only port 8080 exposed"
    else
        echo "✗ Port exposure configuration incorrect"
    fi
else
    echo "✗ Dockerfile missing"
fi

# Check fly.toml
if [ -f "fly.toml" ]; then
    echo "✓ fly.toml exists"

    # Check if internal port is 8080
    if grep -q "internal_port = 8080" fly.toml; then
        echo "✓ fly.toml configured for port 8080"
    else
        echo "✗ fly.toml not configured for port 8080"
    fi
else
    echo "✗ fly.toml missing"
fi

echo ""
echo "Configuration test completed!"
