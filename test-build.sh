#!/bin/bash

echo "Testing Docker build with optimizations..."
echo "Build context size before .dockerignore:"
du -sh . 2>/dev/null || echo "Could not calculate size"

echo ""
echo "Starting Docker build test..."
docker build -t fixmymind-backend-test . --progress=plain

if [ $? -eq 0 ]; then
    echo "✅ Docker build completed successfully!"
    echo "Image size:"
    docker images fixmymind-backend-test --format "table {{.Repository}}\t{{.Tag}}\t{{.Size}}"
else
    echo "❌ Docker build failed"
    exit 1
fi
