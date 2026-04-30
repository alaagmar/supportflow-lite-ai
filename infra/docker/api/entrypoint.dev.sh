#!/bin/sh
set -e

# Install/update composer dependencies if vendor is missing
if [ ! -d "vendor" ]; then
    echo "[entrypoint.dev] vendor/ not found — running composer install..."
    composer install --no-interaction --prefer-dist
fi

# Fix storage permissions
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "[entrypoint.dev] APP_KEY not set — generating..."
    php artisan key:generate --ansi
fi

exec "$@"
