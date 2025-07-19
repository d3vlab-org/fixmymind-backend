#!/bin/bash

echo "🚀 Deploy na produkcję (Fly.io)..."

fly deploy --config fly.toml --remote-only
