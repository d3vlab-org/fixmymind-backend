#!/bin/bash

echo "🚀 Uruchamianie środowiska TEST (Prosiak + Cloudflare Tunnel)..."

export ENV_FILE=".env.test"

docker compose -f docker-compose.yml -f docker-compose.test.yml up -d --build

# Uruchomienie tunelu Cloudflare
cloudflared tunnel --config ./cloudflared/config.test.yml run
