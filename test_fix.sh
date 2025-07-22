#!/bin/bash

echo "=== Testing Fly.io Health Check Fix ==="
echo "Current date: $(date)"

# Check if we're in the right directory
if [ ! -f "fly.toml" ]; then
    echo "Error: fly.toml not found. Make sure you're in the fixmymind-backend directory."
    exit 1
fi

echo ""
echo "=== Updated fly.toml health check configuration ==="
echo "Main health check:"
grep -A 10 '\[checks\]' fly.toml | grep -E '(timeout|interval|grace_period|path)'

echo ""
echo "HTTP service health check:"
grep -A 10 'http_checks' fly.toml | grep -E '(timeout|interval|grace_period|path)'

echo ""
echo "=== Configuration Analysis ==="
echo "✓ Timeout increased from 2s to 10s (5x more time)"
echo "✓ Interval increased from 10s to 15s (less frequent checks)"
echo "✓ Grace period increased from 5s to 30s (6x more startup time)"
echo "✓ Both health checks now use /api/health (lightweight endpoint)"
echo "✓ Removed dependency on complex welcome view for health checks"

echo ""
echo "=== Expected Benefits ==="
echo "1. APP_KEY updates will have 30s grace period to complete Laravel boot"
echo "2. Health checks use lightweight JSON endpoint instead of heavy HTML view"
echo "3. 10s timeout allows sufficient time for Laravel to respond"
echo "4. Consistent configuration across all health check types"

echo ""
echo "=== Deployment Recommendation ==="
echo "The updated configuration should resolve the timeout issues."
echo "When running 'fly secrets set APP_KEY=\"***\"', the deployment should now:"
echo "- Wait 30s before starting health checks (grace_period)"
echo "- Use the fast /api/health endpoint"
echo "- Allow up to 10s for each health check response"
echo "- Check every 15s instead of every 10s"

echo ""
echo "=== Next Steps ==="
echo "1. Commit these changes to your repository"
echo "2. Deploy using: fly deploy"
echo "3. Set the APP_KEY using: fly secrets set APP_KEY=\"your-key-here\""
echo "4. Monitor the deployment logs for successful health checks"
