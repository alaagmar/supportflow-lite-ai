#!/bin/sh
# wait-for-redis.sh — Block until Redis is accepting connections.
set -e

HOST="${REDIS_HOST:-redis}"
PORT="${REDIS_PORT:-6379}"
RETRIES=30

echo "[wait-for-redis] Waiting for Redis at $HOST:$PORT..."

i=0
until redis-cli -h "$HOST" -p "$PORT" ping | grep -q "PONG"; do
    i=$((i + 1))
    if [ "$i" -ge "$RETRIES" ]; then
        echo "[wait-for-redis] ERROR: Redis not ready after $RETRIES attempts. Exiting."
        exit 1
    fi
    echo "[wait-for-redis] Not ready yet ($i/$RETRIES). Retrying in 2s..."
    sleep 2
done

echo "[wait-for-redis] Redis is ready."
