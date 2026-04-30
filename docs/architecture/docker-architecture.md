# Docker Architecture

## Image Map

| Service       | Image                          | Notes                                    |
|---------------|--------------------------------|------------------------------------------|
| PHP-FPM       | `php:8.4.20-fpm-alpine3.23`   | Laravel API, worker, scheduler runtime   |
| Composer      | `composer:2.9.7`               | Build stage only                         |
| Node.js       | `node:24.15.0-alpine3.23`     | Next.js build + runtime (LTS)            |
| Nginx         | `nginx:1.30.0-alpine3.23`     | Serves Laravel public/ via FPM           |
| Caddy         | `caddy:2.11.2-alpine`         | TLS termination, public reverse proxy    |
| PostgreSQL    | `postgres:18.3-alpine3.23`    | Primary database                         |
| Redis         | `redis:8.6.2-alpine3.23`      | Queues, cache, sessions, rate limiting   |
| Mailpit       | `axllent/mailpit:v1.29.7`     | Dev-only SMTP + web UI                   |

## Key Rules

1. **No `latest` tags** — all images pinned to exact versions
2. **One backend image** — `api`, `worker`, `scheduler` all share the same Dockerfile
3. **Two networks** — `public` (caddy, api-nginx, web) and `private` (internal only)
4. **Named volumes** — postgres_data, redis_data, api_storage, api_public, caddy_data, caddy_config

## Compose File Strategy

- `compose.yaml` — base services, shared across all environments
- `compose.dev.yaml` — dev overrides: live mounts, ports, mailpit, xdebug
- `compose.prod.yaml` — prod overrides: env flags, resource limits

Usage:
```bash
# Dev
docker compose -f compose.yaml -f compose.dev.yaml up

# Prod
docker compose -f compose.yaml -f compose.prod.yaml up -d
```
