# ADR 001 — Monorepo Structure

**Date:** 2026-04-30
**Status:** Accepted

## Context

SupportFlow Lite AI has two main application layers: a Laravel API backend and a Next.js frontend dashboard. Infrastructure configuration (Docker, Nginx, Caddy, PHP ini) is shared and must be version-controlled alongside the apps.

## Decision

Use a **monorepo** with the following layout:

```
supportflow-lite-ai/
├── apps/
│   ├── api/   ← Laravel
│   └── web/   ← Next.js
├── infra/     ← Docker, Nginx, Caddy, PHP, scripts
└── docs/      ← Architecture docs, ADRs
```

## Rationale

- Docker build context is the repo root, so `Dockerfile` can copy from both `apps/api` and `infra/` in one build
- Infra changes and app changes are always in sync — no cross-repo coordination
- Single Git history for the full system — simpler for a portfolio project
- Compose files, Makefile, and `.env` live at root with clear ownership

## Consequences

- Both apps are deployed together (acceptable for this project scope)
- If the project scales, `apps/` can be split to separate repos without major refactoring
