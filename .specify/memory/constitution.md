<!--
Sync Impact Report
- Version change: template -> 1.0.0
- Modified principles:
  - Principle slot 1 -> I. Docker-First Execution
  - Principle slot 2 -> II. Monorepo Boundary Integrity
  - Principle slot 3 -> III. Tenant Safety And Role Enforcement
  - Principle slot 4 -> IV. Asynchronous, Validated AI Processing
  - Principle slot 5 -> V. Minimal Changes, Verified Delivery
- Added sections:
  - Implementation Constraints
  - Delivery Workflow
- Removed sections:
  - None
- Templates requiring updates:
  - ✅ .specify/templates/plan-template.md
  - ✅ .specify/templates/spec-template.md
  - ✅ .specify/templates/tasks-template.md
  - ✅ README.md
- Follow-up TODOs:
  - None
-->

# SupportFlow Lite AI Constitution

## Core Principles

### I. Docker-First Execution
All development, test, build, migration, and operational workflows MUST run through
repository-approved Docker commands such as `make` targets or
`docker compose ... exec`. Contributors MUST NOT introduce host-local workflows for
Composer, npm, Artisan, Next.js, queue workers, or tests. Destructive runtime
operations such as volume resets or fresh database rebuilds MUST have explicit
approval before execution. Rationale: runtime parity, pinned image behavior, and
first-start automation depend on the containerized stack.

### II. Monorepo Boundary Integrity
Laravel in `apps/api` MUST remain the system of record for authentication, tenant
data, queues, policies, AI integrations, and migrations. Next.js in `apps/web`
MUST remain an API consumer and MUST NOT access PostgreSQL, Redis, or Laravel
storage directly. Infrastructure topology and runtime configuration MUST stay in
`infra/` and the Compose files, and new work MUST fit an existing bounded context
or define a clearly owned new one. Rationale: clear ownership prevents cross-layer
coupling and keeps the monorepo maintainable.

### III. Tenant Safety And Role Enforcement
Workspace-owned reads and writes MUST be scoped through the authenticated user's
workspace membership before data access. Authorization MUST be enforced with
Sanctum plus Laravel Policies or Form Requests; frontend role checks are UX only
and MUST NOT be treated as authorization. Tenant-owned tables MUST include a
non-null `workspace_id`, foreign keys, and useful indexes, and non-member access
MUST prefer `404` responses to avoid leaking tenant existence. Rationale:
SupportFlow Lite AI is a multi-tenant product, so tenant isolation failures are
security and correctness defects.

### IV. Asynchronous, Validated AI Processing
Long-running AI work MUST execute through Laravel jobs backed by Redis and MUST
NOT run inline in HTTP request handlers. External AI providers MUST be called
behind backend-owned interfaces or services, and every AI response MUST be
validated before persistence or display. Role-prefixed routes may expose portal
entry points for clarity, but shared module behavior MUST stay centralized rather
than duplicated by role. Rationale: queued, validated AI processing is required
for retry safety, provider portability, and predictable user-facing behavior.

### V. Minimal Changes, Verified Delivery
Each change MUST be the smallest implementation that fully solves the requested
problem. Contributors MUST inspect relevant files before editing, avoid
speculative abstractions or dependencies, and preserve existing architecture
unless an approved refactor is part of the work. Every completed change MUST
include the smallest relevant Docker-based verification available, plus an
explicit report of changed files, checks run, skipped checks, and remaining
risks. Rationale: focused changes reduce regression risk and keep reviewable
progress honest.

## Implementation Constraints

- Backend work MUST use Laravel 12 conventions inside `apps/api`.
- Frontend work MUST use the Next.js App Router inside `apps/web`, preserve
  `output: "standalone"`, and expose browser-visible environment values only
  through `NEXT_PUBLIC_*` variables.
- PostgreSQL is the only application database target; SQLite and MySQL
  compatibility paths MUST NOT be introduced.
- Redis is the intended queue, cache, session, and lock backend; new long-running
  flows MUST use asynchronous jobs.
- Docker image tags MUST remain pinned through the root `.env*` variables;
  floating tags such as `latest` are forbidden.
- Real secrets, runtime `.env` files, API keys, private keys, and similar
  credentials MUST NOT be committed. Only safe example defaults may be
  documented in `*.env.example` files.
- New dependencies, schema changes, auth changes, tenant scoping changes, AI
  provider changes, or infrastructure topology changes MUST be explicitly
  justified in the implementation plan and review notes.

## Delivery Workflow

1. Implementation plans MUST include a constitution check for Docker-only
   execution, monorepo ownership, tenant-safety coverage, AI and queue boundaries
   when applicable, and the verification commands that will prove the change.
2. Feature specifications MUST document user stories, edge cases, and any
   constitutional constraints that affect scope, such as tenant isolation, async
   AI handling, or no-host-workflow requirements.
3. Task lists MUST include the validation work required by the change: backend
   tests for backend or domain behavior, frontend lint and build verification for
   frontend changes until a frontend test runner exists, and Docker or Compose
   validation for infrastructure edits.
4. Reviews MUST treat missing tenant isolation, missing authorization, skipped
   required verification, unvalidated AI payload handling, or host-local workflow
   instructions as constitution violations.
5. Runtime guidance in `AGENTS.md`, `README.md`, and `docs/engineering/*.md` may
   add detail, but they MUST NOT conflict with this constitution.

## Governance

This constitution is the authoritative project policy for SupportFlow Lite AI.
When another document conflicts with it, this constitution takes precedence.

Amendments MUST update `.specify/memory/constitution.md` and the dependent
Specify templates in the same change when workflow expectations are affected.
Every amendment MUST include a Sync Impact Report summarizing changed principles,
added or removed sections, affected templates, and any deferred follow-up items.

Versioning follows semantic versioning for governance:

- MAJOR: remove a principle, redefine a principle in a backward-incompatible
  way, or change governance obligations substantially.
- MINOR: add a principle or materially expand required workflow or constraint
  coverage.
- PATCH: clarify wording, fix typos, or make non-semantic refinements.

Compliance review is mandatory for every plan, task list, implementation, and
review. Contributors MUST verify constitution alignment before coding, re-check
it before merge, and record any justified exceptions in the implementation plan's
complexity tracking section. Runtime development guidance lives in `AGENTS.md`
and `docs/engineering/*.md`.

**Version**: 1.0.0 | **Ratified**: 2026-05-05 | **Last Amended**: 2026-05-05
