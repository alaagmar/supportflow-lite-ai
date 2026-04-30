# ADR 005 — Pinned Lightweight Docker Images

**Date:** 2026-04-30
**Status:** Accepted

## Context

Docker images can be referenced by floating tags like `latest` or by exact pinned versions. Most tutorials use `latest` for convenience.

## Decision

**All Docker images are pinned to exact, lightweight Alpine-based tags.**

| Service     | Image                          |
|-------------|--------------------------------|
| PHP-FPM     | `php:8.4.20-fpm-alpine3.23`   |
| Composer    | `composer:2.9.7`               |
| Node.js     | `node:24.15.0-alpine3.23`     |
| Nginx       | `nginx:1.30.0-alpine3.23`     |
| Caddy       | `caddy:2.11.2-alpine`         |
| PostgreSQL  | `postgres:18.3-alpine3.23`    |
| Redis       | `redis:8.6.2-alpine3.23`      |
| Mailpit     | `axllent/mailpit:v1.29.7`     |

Version pins are centralized in `.env` (root level) so upgrades are a single-file change and can be intentionally reviewed.

## Rationale

- **Reproducibility**: the same image is guaranteed to build identically on any machine or CI runner
- **Security**: explicit versions make it obvious when you are or aren't getting security patches
- **Alpine base**: all images use Alpine Linux — significantly smaller footprint (PHP Alpine ~70MB vs ~500MB Debian)
- **Node LTS**: Node 24 is the current Active LTS line — Node 25 (`Current`) is intentionally avoided for production use

## Consequences

- Developers must update `.env` intentionally to pull new versions
- CI should fail if a non-pinned image is referenced
- Upgrading requires testing the new image, not just bumping a tag
