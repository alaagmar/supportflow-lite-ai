---
name: docker-change
description: Use when changing Docker Compose, Dockerfiles, entrypoints, healthchecks, Nginx, Caddy, Makefile targets, or env examples.
compatibility: opencode
---

# What I do

I protect the Docker-first workflow, pinned images, service topology, private/public networks, and container-owned commands.

# When to use me

- Editing `compose*.yaml`, `infra/`, `Makefile`, or root/app env examples.
- Changing image versions or service dependencies.
- Adding a new service or public port.
- Debugging startup, healthcheck, Nginx, Caddy, PHP-FPM, or Next runtime behavior.

# Required context

- `AGENTS.md` and `docs/engineering/architecture.md`.
- `docs/architecture/docker-architecture.md` and ADR 005.
- Root `.env.example`, `.env.development.example`, `.env.production.example`.
- Compose files, Dockerfiles, entrypoints, healthchecks, scripts, Nginx/Caddy config.

# Workflow

1. Identify which service and environment are affected.
2. Check image pinning and env variable sources.
3. Preserve private/public network boundaries.
4. Keep development conveniences in `compose.dev.yaml` and production concerns in `compose.prod.yaml`.
5. Ensure commands remain Docker-based.
6. Run `docker compose -f compose.yaml -f compose.dev.yaml config --quiet`.
7. Run affected service checks if the stack is available.

# Project rules

- No `latest` or broad image tags.
- PostgreSQL and Redis must not be exposed publicly.
- API PHP-FPM, worker, and scheduler are private services.
- Destructive volume commands require explicit user approval.
- Shell scripts in `infra/` should stay POSIX `sh` unless already otherwise required.

# Mistakes to avoid

- Changing a Dockerfile without updating docs or env examples when image args change.
- Adding host-local setup commands to compensate for container issues.
- Breaking first-start Composer/npm install behavior.
- Forgetting production impact of frontend standalone output.

# Completion checklist

- Compose config validates.
- Image tags are pinned through env variables.
- Ports and networks are intentional.
- Docs and Make targets still match actual commands.
