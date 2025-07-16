#!/bin/bash

echo "🚀 Uruchamianie środowiska DEV (localhost + Cloudflare Tunnel)..."

export ENV_FILE=".env.dev"

docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build

# Uruchomienie tunelu Cloudflare
cloudflared tunnel --config ./cloudflared/config.dev.yml run
