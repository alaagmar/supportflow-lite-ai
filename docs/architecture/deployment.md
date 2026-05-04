# Deployment Guide

## Prerequisites

- A Linux VPS (Ubuntu 24.04 recommended)
- Docker + Docker Compose v2 installed
- A domain name with DNS pointing to your server
- Port 80 and 443 open

All deployment operations are Docker-based. Do not run Composer, npm, Artisan, or Next.js directly on the host.

## Steps

### 1. Prepare the repository

Place the repository on the server and run commands from the repository root.

### 2. Configure environment

Create `.env` from `.env.production.example`, then set real image pins, database credentials, AI keys, and domain names.

### 3. Set up apps/api/.env

Create `apps/api/.env` from `apps/api/.env.example`, then edit it with production values.

### 4. Set up apps/web/.env

Create `apps/web/.env` from `apps/web/.env.example`, then set `NEXT_PUBLIC_API_URL` to the production API URL.

### 5. Update Caddyfile

Edit `infra/caddy/Caddyfile` and replace `yourdomain.com` with your real domain.

### 6. Build and start

```bash
make prod
```

### 7. Run migrations

```bash
docker compose -f compose.yaml -f compose.prod.yaml exec api php artisan migrate --force
```

### 8. Generate app key (first deploy only)

```bash
docker compose -f compose.yaml -f compose.prod.yaml exec api php artisan key:generate
```

## Notes

- Caddy handles TLS automatically via Let's Encrypt
- Mailpit is NOT included in prod — remove it from compose if needed
- Never run `--seed` in production with demo data
