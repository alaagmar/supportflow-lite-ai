#!/bin/sh
set -e

echo "[worker] Starting Laravel queue worker..."
exec php artisan queue:work redis \
    --sleep=3 \
    --tries=3 \
    --timeout=180 \
    --max-jobs=1000 \
    --max-time=3600
