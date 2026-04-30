#!/bin/sh
# seed.sh — Run database seeders.
set -e

echo "[seed] Running database seeders..."
php artisan db:seed --no-interaction
echo "[seed] Done."
