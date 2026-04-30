# Deployment Guide

## Prerequisites

- A Linux VPS (Ubuntu 24.04 recommended)
- Docker + Docker Compose v2 installed
- A domain name with DNS pointing to your server
- Port 80 and 443 open

## Steps

### 1. Clone the repository

```bash
git clone https://github.com/yourusername/supportflow-lite-ai.git
cd supportflow-lite-ai
```

### 2. Configure environment

```bash
cp .env.production.example .env
# Edit .env — set real DB password, Mistral key, domain names
```

### 3. Set up apps/api/.env

```bash
cp apps/api/.env.example apps/api/.env
# Edit with production values
```

### 4. Set up apps/web/.env

```bash
cp apps/web/.env.example apps/web/.env
# Edit NEXT_PUBLIC_API_URL to your real domain
```

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
