#!/bin/bash

echo "=== Reproducing Fly.io Health Check Issue ==="
echo "Current date: $(date)"

# Check if we're in the right directory
if [ ! -f "fly.toml" ]; then
    echo "Error: fly.toml not found. Make sure you're in the fixmymind-backend directory."
    exit 1
fi

echo ""
echo "=== Current fly.toml health check configuration ==="
echo "Health check timeout: $(grep -A 5 '\[checks\]' fly.toml | grep 'timeout')"
echo "Health check interval: $(grep -A 5 '\[checks\]' fly.toml | grep 'interval')"
echo "Health check path: $(grep -A 5 '\[checks\]' fly.toml | grep 'path')"

echo ""
echo "=== HTTP service health check configuration ==="
echo "HTTP service timeout: $(grep -A 10 'http_checks' fly.toml | grep 'timeout')"
echo "HTTP service interval: $(grep -A 10 'http_checks' fly.toml | grep 'interval')"
echo "HTTP service path: $(grep -A 10 'http_checks' fly.toml | grep 'path')"

echo ""
echo "=== Testing local health endpoints ==="

# Start Laravel development server in background
echo "Starting Laravel development server..."
php artisan serve --host=0.0.0.0 --port=8080 &
SERVER_PID=$!

# Wait for server to start
sleep 5

echo "Testing health endpoints..."

# Test root endpoint
echo "Testing / endpoint:"
curl -w "Time: %{time_total}s, Status: %{http_code}\n" -s -o /dev/null http://localhost:8080/ || echo "Failed to connect to / endpoint"

# Test API health endpoint
echo "Testing /api/health endpoint:"
curl -w "Time: %{time_total}s, Status: %{http_code}\n" -s -o /dev/null http://localhost:8080/api/health || echo "Failed to connect to /api/health endpoint"

# Clean up
kill $SERVER_PID 2>/dev/null

echo ""
echo "=== Analysis ==="
echo "The issue appears to be:"
echo "1. Health check timeout is set to 2s, which may be too aggressive"
echo "2. The / endpoint renders a complex view that might be slow"
echo "3. APP_KEY issues during deployment could prevent Laravel from booting"
echo ""
echo "Recommended fixes:"
echo "1. Increase health check timeout to 10s"
echo "2. Use only /api/health endpoint for health checks"
echo "3. Ensure APP_KEY is properly set before health checks start"
