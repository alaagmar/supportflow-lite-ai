#!/bin/sh
set -e

# Wait for dependencies
/var/www/html/infra/scripts/wait-for-db.sh
/var/www/html/infra/scripts/wait-for-redis.sh

# Run migrations on startup (safe with --force in prod)
php artisan migrate --force --no-interaction

# Fix storage permissions in case volume was freshly mounted
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

exec "$@"
