#!/bin/sh
# reset-dev.sh — Tear down volumes, rebuild, and re-seed for a clean dev environment.
# WARNING: This destroys all local data including the database.
set -e

echo ""
echo "⚠️  This will destroy all local volumes and data."
printf "Are you sure? [y/N] "
read -r confirm

if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
    echo "Aborted."
    exit 0
fi

echo "[reset-dev] Stopping containers and removing volumes..."
docker compose -f compose.yaml -f compose.dev.yaml down -v

echo "[reset-dev] Rebuilding and starting services..."
docker compose -f compose.yaml -f compose.dev.yaml up -d --build

echo "[reset-dev] Waiting for services to be healthy..."
sleep 10

echo "[reset-dev] Running fresh migrations and seeders..."
docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan migrate:fresh --seed --no-interaction

echo ""
echo "✅  Dev environment reset complete."
echo "   Next.js:  http://localhost:3000"
echo "   Laravel:  http://localhost:8080"
echo "   Mailpit:  http://localhost:8025"
