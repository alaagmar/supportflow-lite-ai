#!/bin/sh
# migrate.sh — Run Laravel migrations safely.
set -e

echo "[migrate] Running database migrations..."
php artisan migrate --force --no-interaction
echo "[migrate] Done."
