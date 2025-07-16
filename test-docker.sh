#!/bin/bash

echo "=== Testing Dockerfile Configuration ==="

# Check if all required files exist
echo "Checking required files..."

if [ -f "Dockerfile" ]; then
    echo "✓ Dockerfile exists"
else
    echo "✗ Dockerfile missing"
    exit 1
fi

if [ -f "docker/supervisord.conf" ]; then
    echo "✓ supervisord.conf exists"
else
    echo "✗ supervisord.conf missing"
    exit 1
fi

if [ -f "docker/start.sh" ]; then
    echo "✓ start.sh exists"
else
    echo "✗ start.sh missing"
    exit 1
fi

# Check if start.sh is executable
if [ -x "docker/start.sh" ]; then
    echo "✓ start.sh is executable"
else
    echo "✗ start.sh is not executable"
fi

# Validate Dockerfile content
echo -e "\nValidating Dockerfile content..."

if grep -q "FROM php:8.2-fpm-alpine" Dockerfile; then
    echo "✓ Uses PHP 8.2"
else
    echo "✗ Does not use PHP 8.2"
fi

if grep -q "redis" Dockerfile; then
    echo "✓ Includes Redis"
else
    echo "✗ Redis not found"
fi

if grep -q "supervisor" Dockerfile; then
    echo "✓ Includes Supervisor"
else
    echo "✗ Supervisor not found"
fi

if grep -q "composer" Dockerfile; then
    echo "✓ Includes Composer"
else
    echo "✗ Composer not found"
fi

if grep -q "APP_ROLE" Dockerfile; then
    echo "✓ APP_ROLE environment variable configured"
else
    echo "✗ APP_ROLE not found"
fi

# Validate start.sh script
echo -e "\nValidating start.sh script..."

if grep -q "artisan serve" docker/start.sh; then
    echo "✓ Supports artisan serve"
else
    echo "✗ artisan serve not found"
fi

if grep -q "queue:work" docker/start.sh; then
    echo "✓ Supports queue workers"
else
    echo "✗ queue:work not found"
fi

if grep -q "php-fpm" docker/start.sh; then
    echo "✓ Supports PHP-FPM"
else
    echo "✗ PHP-FPM not found"
fi

# Check APP_ROLE cases
echo -e "\nChecking APP_ROLE cases..."
for role in "web" "dev" "test" "worker" "all"; do
    if grep -q "\"$role\"" docker/start.sh; then
        echo "✓ APP_ROLE=$role supported"
    else
        echo "✗ APP_ROLE=$role not supported"
    fi
done

echo -e "\n=== Test completed ==="
