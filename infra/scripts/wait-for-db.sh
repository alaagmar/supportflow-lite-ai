#!/bin/sh
# wait-for-db.sh — Block until PostgreSQL is accepting connections.
set -e

HOST="${DB_HOST:-postgres}"
PORT="${DB_PORT:-5432}"
USER="${DB_USERNAME:-supportflow}"
DB="${DB_DATABASE:-supportflow}"
RETRIES=30

echo "[wait-for-db] Waiting for PostgreSQL at $HOST:$PORT..."

i=0
until pg_isready -h "$HOST" -p "$PORT" -U "$USER" -d "$DB" -q; do
    i=$((i + 1))
    if [ "$i" -ge "$RETRIES" ]; then
        echo "[wait-for-db] ERROR: PostgreSQL not ready after $RETRIES attempts. Exiting."
        exit 1
    fi
    echo "[wait-for-db] Not ready yet ($i/$RETRIES). Retrying in 2s..."
    sleep 2
done

echo "[wait-for-db] PostgreSQL is ready."
