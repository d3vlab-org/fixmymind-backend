#!/bin/bash

echo "=== Testing Nginx Configuration ==="

# Check if nginx.conf exists
if [ -f "docker/nginx.conf" ]; then
    echo "✓ nginx.conf exists"
else
    echo "✗ nginx.conf missing"
    exit 1
fi

# Check if nginx is configured to listen on 0.0.0.0:8080
if grep -q "listen 0.0.0.0:8080;" docker/nginx.conf; then
    echo "✓ Nginx configured to listen on 0.0.0.0:8080"
else
    echo "✗ Nginx not configured to listen on 0.0.0.0:8080"
    echo "Current listen configuration:"
    grep "listen" docker/nginx.conf
    exit 1
fi

# Check if PHP-FPM is configured correctly
if grep -q "fastcgi_pass 127.0.0.1:9000;" docker/nginx.conf; then
    echo "✓ PHP-FPM configured correctly (127.0.0.1:9000)"
else
    echo "✗ PHP-FPM configuration issue"
    echo "Current fastcgi_pass configuration:"
    grep "fastcgi_pass" docker/nginx.conf
fi

# Check if document root is set correctly
if grep -q "root /var/www/html/public;" docker/nginx.conf; then
    echo "✓ Document root configured correctly"
else
    echo "✗ Document root configuration issue"
    echo "Current root configuration:"
    grep "root" docker/nginx.conf
fi

echo -e "\n=== Nginx Configuration Test Completed ==="
