# SupportFlow Lite AI — Antigravity Agent

## Identity

You are the engineering agent for **SupportFlow Lite AI**, a multi-tenant AI support triage SaaS built with Laravel 12, Next.js 15, PostgreSQL, and Redis. You have deep context about this project's architecture, conventions, and decisions.

## Project Context

- **Backend**: Laravel 12 API in `apps/api/` — Sanctum auth, Redis queues, Scheduler, Policies
- **Frontend**: Next.js 15 App Router in `apps/web/` — Tailwind CSS, shadcn/ui, TanStack Table
- **AI Layer**: Provider-agnostic interface — `MistralAiProvider` primary, `MockAiProvider` fallback
- **Database**: PostgreSQL 18 — every tenant record has `workspace_id`
- **Queue**: Redis — all AI processing runs through queue jobs, never synchronously
- **Docker**: Pinned Alpine images — see `docker-structure.md` and `docs/architecture/docker-architecture.md`
- **Infra**: `infra/` directory — Dockerfiles, Nginx, Caddy, PHP ini, shell scripts

## Core Rules (always enforce)

See `.agent/rules.md` for the full rule set. Summary:

1. **Never call Mistral directly from a controller** — always via `AiProviderInterface`
2. **Never process tickets synchronously** — always dispatch a queue job
3. **Every tenant record must have `workspace_id`** — no orphaned rows
4. **Every AI run must be logged** in `ai_runs` table with full input/output/latency
5. **Validate all Mistral JSON output** before saving — mark `ai_run` failed on invalid JSON
6. **Never use `latest` Docker image tags** — update `.env` image pins intentionally
7. **No direct AI-to-production writes** for risky actions — approval gate always required

## Skills

Load these skill files when working on the corresponding tasks:

- Laravel queue jobs → `.agent/skills/laravel-queue-job.md`
- AI provider integration → `.agent/skills/ai-provider-abstraction.md`
- Multi-tenant data isolation → `.agent/skills/tenant-isolation.md`
- Docker and infra changes → `.agent/skills/docker-workflow.md`

## Architecture Docs

Always reference before making structural decisions:

- `docs/architecture/system-overview.md`
- `docs/architecture/ai-pipeline.md`
- `docs/architecture/database-design.md`
- `docs/architecture/docker-architecture.md`
- `docs/decisions/` — all ADRs

## Development Commands

```bash
make dev          # Start all services
make api-shell    # Shell into Laravel container
make migrate      # Run migrations
make fresh        # Wipe and re-seed
make test-api     # Run Laravel tests
make queue-logs   # Watch worker output
```
