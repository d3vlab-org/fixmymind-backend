#!/bin/bash

echo "🚀 Uruchamianie środowiska DEV (localhost + Cloudflare Tunnel)..."

export ENV_FILE=".env.local"

docker compose -f docker-compose.yml up -d --build

# Uruchomienie tunelu Cloudflare
cloudflared tunnel --config ~/.cloudflared/config.yml run fixmymind-dev
