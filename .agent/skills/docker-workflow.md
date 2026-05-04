# Skill: Docker Workflow

Use this skill when modifying infrastructure, Dockerfiles, Compose files, or environment configuration.

---

## The Core Rule

**Never use `latest` or floating tags.** All image versions are pinned in `.env` (root level).

```bash
# Correct — reads from .env
image: ${PHP_IMAGE}

# Wrong
image: php:latest
image: php:8.4-fpm
```

---

## Updating an Image Version

1. Edit `.env` (root level) — update the relevant pin:
   ```env
   PHP_IMAGE=php:8.4.21-fpm-alpine3.23   # was 8.4.20
   ```
2. Rebuild intentionally:
   ```bash
   make down && make dev
   ```
3. Test that all containers start healthy
4. Commit `.env.example` with the updated pin (never commit `.env` with secrets)

---

## Compose File Strategy

| File | Purpose |
|------|---------|
| `compose.yaml` | Base service definitions — shared across all envs |
| `compose.dev.yaml` | Dev overrides: live mounts, exposed ports, mailpit, xdebug |
| `compose.prod.yaml` | Prod overrides: env flags, resource limits |

Always layer:
```bash
# Dev
docker compose -f compose.yaml -f compose.dev.yaml up

# Prod
docker compose -f compose.yaml -f compose.prod.yaml up -d
```

---

## Network Topology

```
public network:   caddy, api-nginx, web, mailpit(dev)
private network:  api, worker, scheduler, postgres, redis
```

- `api`, `worker`, `scheduler` are **never directly exposed** — only reachable via `api-nginx`
- `postgres` and `redis` are **never exposed** — no port mappings in `compose.yaml`

---

## Adding a New Service

1. Add to `compose.yaml` under `services:`
2. Use `${IMAGE_NAME}` for the image — add the pin to `.env`
3. Assign to the correct network (`public` or `private`)
4. If it's dev-only (like mailpit), put it in `compose.dev.yaml` only
5. Add a `healthcheck` if the service is a dependency of another

---

## Shell Access

```bash
make api-shell       # sh into Laravel container
make web-shell       # sh into Next.js container
make postgres-shell  # psql into PostgreSQL

# Or directly:
docker compose -f compose.yaml -f compose.dev.yaml exec api sh
docker compose -f compose.yaml -f compose.dev.yaml exec postgres psql -U supportflow -d supportflow
```

---

## Common Issues

### Entrypoint scripts not executable
Entrypoint permissions should be handled in Dockerfiles or committed file modes, not as a host-local setup step.

### Volume permission issues (Laravel storage)
The `api_storage` volume is mounted at `/var/www/html/storage`.
The `www-data` user must own this. The entrypoint script handles this:
```sh
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
```

### Vendor not found on first dev start
The dev entrypoint auto-runs `composer install` if `vendor/` is missing.
Alternatively: `make composer-install`

### Database not ready on startup
The `entrypoint.prod.sh` calls `wait-for-db.sh` first.
In dev, Compose healthchecks handle ordering.

---

## Makefile Quick Reference

```bash
make dev          # Start all services (with build)
make dev-d        # Start detached
make down         # Stop
make down-v       # Stop + remove volumes (DESTRUCTIVE)
make api-shell    # Shell into api
make migrate      # Run migrations
make fresh        # migrate:fresh --seed
make queue-logs   # Follow worker logs
make reset-dev    # Full teardown + rebuild + seed (interactive prompt)
```
