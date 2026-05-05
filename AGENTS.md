# SupportFlow Lite AI Agent Instructions

## Project Overview

SupportFlow Lite AI is a scaffolded multi-tenant AI support triage SaaS. The intended flow is ticket intake, queued AI classification, policy retrieval, draft reply generation, and human review.

The repository is a Docker-first monorepo:

- Backend: Laravel 12 API in `apps/api`, Sanctum installed, API routing enabled, PostgreSQL/Redis/Mailpit env defaults.
- Frontend: Next.js 15 App Router in `apps/web`, TypeScript, Tailwind CSS 4, ESLint, standalone output for Docker production builds.
- Infrastructure: Docker Compose, PHP-FPM, Nginx, Caddy, PostgreSQL 18, Redis 8, Mailpit, scripts in `infra/`.
- Current state: Identity, Workspace, and Ticket foundations are implemented with role-prefixed auth/workspace/ticket APIs, workspace membership roles, owner/admin/staff frontend ticket entry points, and backend feature tests. AI processing pipeline with queued jobs, provider abstraction, mock fallback, and frontend review UI are implemented. Policy knowledge base, audit/analytics, billing mock, and team invitation/member management are still pending.

All development and production workflows are Docker-based. Do not run Composer, npm, Artisan, Next.js, queue workers, or tests directly on the host.

## Essential Commands

Run commands from the repository root unless stated otherwise.

- Install dependencies: `make dev` installs missing API Composer deps and web npm deps inside containers on first start.
- API dependencies only: `make composer-install`.
- Web dependencies only: `make npm-install`.
- Start dev stack: `make dev` or detached with `make dev-d`.
- Stop dev stack: `make down`.
- Destructive reset: `make down-v` or `make reset-dev` only with explicit user intent.
- Production start: `make prod`.
- Production stop: `make prod-down`.
- API tests: `make test-api`.
- Focused API test: `docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan test --filter=TestName`.
- Web tests: `make test-web` exists, but `apps/web/package.json` currently has no `test` script.
- API format: `docker compose -f compose.yaml -f compose.dev.yaml exec api ./vendor/bin/pint`.
- Frontend lint: `docker compose -f compose.yaml -f compose.dev.yaml exec web npm run lint`.
- Frontend build/typecheck: `docker compose -f compose.yaml -f compose.dev.yaml exec web npm run build`.
- Frontend standalone build: same as frontend build; `next.config.ts` sets `output: "standalone"`.
- Compose validation: `docker compose -f compose.yaml -f compose.dev.yaml config --quiet`.
- Database migrate: `make migrate`.
- Database fresh with seed: `make fresh`.
- Queue logs: `make queue-logs`.

No root package manager is configured. API package management lives in `apps/api/composer.json`. Web package management lives in `apps/web/package.json` and `apps/web/package-lock.json`, so use npm inside Docker.

## Repository Structure

- `apps/api/`: Laravel application code. Put controllers, requests, resources, policies, jobs, models, services, migrations, factories, seeders, and feature/unit tests here. Do not put frontend code or Docker config here.
- `apps/web/`: Next.js application code. Put App Router pages/layouts/components, frontend utilities, and browser/server API clients here. Do not call the database from here.
- `infra/`: Dockerfiles, entrypoints, healthchecks, PHP ini, Nginx, Caddy, and shell scripts. Keep scripts POSIX `sh` unless the file already requires Bash.
- `docs/architecture/`: accepted architecture and ADRs. Treat AI pipeline and database design docs as intended design where code has not been implemented yet.
- `docs/engineering/`: engineering standards used by OpenCode and contributors.
- `.opencode/`: project-local OpenCode skills, commands, and optional read-only agents.
- `.agent/`: legacy agent instructions. Some content is aspirational and predates the scaffold; prefer `AGENTS.md` and `docs/engineering/*` for OpenCode sessions.

## Product Roles

These roles come from `docs/copy1.md` and define authorization expectations across every workspace-scoped module:

- Owner: can do everything in the workspace, including workspace management, billing mock/settings, team members, AI provider settings, usage dashboard, and owner-only destructive/settings actions.
- Admin: can manage policy documents, ticket queues, workflow rules, team assignments, and tickets; process AI; view logs; and invite agents. Admins cannot delete workspaces or manage owner-only billing/provider settings.
- Agent: can view and work assigned tickets, process tickets, review/edit/approve/reject AI drafts, assign tickets where policy allows, and resolve tickets. Agents cannot manage policies, team membership, provider settings, or owner-only workspace settings.
- Viewer: can view tickets, analytics, and audit logs. Viewers cannot edit, approve, process, assign, delete, or mutate tenant data.

Roles are workspace membership capabilities, not separate application silos. Do not duplicate database tables, domain use cases, repositories, policies, or Next.js workflow trees per role when the underlying domain operation is the same. Role-prefixed portal API entry points are allowed for product/API clarity, but they must delegate to shared module behavior rather than copy business logic. Enforce role differences with Laravel Policies/Gates and Form Requests on the backend, then mirror the allowed actions in the UI as affordances only.

Current portal access rules:

- Owner portal: `owner` memberships only.
- Admin portal: `owner` and `admin` memberships.
- Staff portal: `owner`, `admin`, `agent`, and `viewer` memberships.
- Workspace creation is owner-only. Viewer access is read-only even when authenticated through the staff portal.

## Modular DDD Boundaries

Build new product work as thin vertical slices inside bounded contexts rather than broad technical layers. Current intended modules are:

- Identity and Workspace: users, workspaces, workspace members, role assignment, workspace switching, owner/admin settings.
- Ticketing: tickets, ticket messages, comments, assignment, status changes, human review actions, ticket queue filters.
- Policy Knowledge Base: policy documents, document ingestion, chunking, keyword retrieval, evidence display.
- AI Processing: provider abstraction, Mistral/mock providers, prompt templates, JSON validation, queued ticket pipeline, ticket AI output.
- Audit and Analytics: audit logs, AI run timelines, usage metrics, dashboard summaries.
- Billing Mock and Provider Settings: owner-only configuration screens and placeholder billing state until real billing is approved.

Laravel 12 module guidance:

- Keep framework entry points conventional: routes in `routes/api.php`, controllers/requests/resources under `app/Http`, policies under `app/Policies`, models under `app/Models`, jobs under `app/Jobs`, migrations/factories/seeders under `database`.
- Put reusable domain behavior under `app/Domain/<Module>` when a module has real business logic, such as actions, services, DTOs, value objects, enums, prompt builders, or retrieval logic. Do not create empty domain layers before behavior exists.
- Use Eloquent relationships from the owning aggregate/root model where practical, for example workspace-scoped ticket and policy queries. Never retrieve tenant data globally and then check access afterward.
- Keep external integrations behind module-owned interfaces in the domain/service layer. Controllers should orchestrate validation, authorization, delegation, and resources only.
- Use single-action invokable controllers for new JSON API endpoints. Each endpoint controller should validate, authorize, delegate to a use case or scoped Eloquent relationship, and return an API Resource through `App\Http\Responses\ApiResponse` where practical.
- Keep role-prefixed controllers thin. Owner/Admin/Staff controllers may exist as portal entry points, but shared behavior belongs in `app/Domain/<Module>/UseCases`, contracts, repositories, resources, policies, and requests.
- Put app-wide custom exceptions under `app/Exceptions`. Domain classes may throw them, but HTTP rendering should stay consistent with Laravel JSON responses or `ApiResponse::error(...)` for custom app errors.
- Use `AuthSessionResource`, `CurrentSessionResource`, `UserResource`, and `WorkspaceResource` patterns for Identity/Workspace responses. Preserve Laravel's standard validation error shape.

Next.js 15 module guidance:

- Keep route UI in `apps/web/src/app` and organize dashboard routes by workflow/module, such as `tickets`, `policies`, `ai-runs`, `audit-logs`, `settings`, and `team`.
- Extract reusable module code only when needed into feature folders such as `src/features/tickets`, `src/features/policies`, `src/features/ai-runs`, or shared typed API utilities. Do not add global state or a UI/data library just to model roles.
- Use Server Components by default. Add Client Components only for forms, local interactivity, browser APIs, optimistic interaction, or accessible dynamic controls.
- Treat frontend role checks as UX only. The Laravel API remains the source of truth for authorization and tenant isolation.

## Coding Rules

- Preserve the monorepo boundary: Laravel owns data, auth, queues, policies, AI provider integration, and migrations; Next.js owns UI and client/server calls to the API.
- Preserve module boundaries. Every new route, model, component, job, policy, and test should have an obvious owning module from the bounded contexts above.
- Keep Docker-only workflows. If docs or scripts add commands, they must run through `make` or `docker compose ... exec` unless they are repository inspection commands like `git status`.
- PostgreSQL is the only app database target. Do not add SQLite workflows, SQLite test defaults, MySQL-specific migrations, or host database assumptions.
- Redis is the intended queue/cache/session backend. AI ticket processing must be asynchronous through Laravel jobs, not controller-side synchronous work.
- Use Sanctum for first-party API auth. Workspace-scoped API routes must be authenticated and authorized before touching tenant data.
- Role-specific API route groups currently use `/api/owner/...`, `/api/admin/...`, and `/api/staff/...`. Add new role-prefixed routes only as portal entry points; do not fork the domain model or duplicate business logic behind them.
- Apply the Owner/Admin/Agent/Viewer role matrix through policies for every workspace-scoped capability; never trust client-side role checks or client-provided `workspace_id` as authorization.
- Scope workspace reads through the authenticated user's memberships before returning tenant data. Prefer `404` for non-member workspace access so existence is not leaked.
- Tenant-owned database tables must include non-null `workspace_id`, a foreign key, and useful indexes. Queries for tenant data must be scoped to the current workspace.
- Do not call Mistral or any AI provider from controllers, React components, routes, or migrations. Add provider calls behind a Laravel service interface and validate JSON before saving.
- Laravel API responses should use Form Requests for validation and API Resources for model output once domain endpoints are added.
- Use `ApiResponse` for successful resource wrappers, no-content responses, and custom app errors in new API code. Do not replace Laravel's built-in validation/auth/404 JSON shapes unless there is a specific compatibility reason.
- Keep Eloquent access in Laravel controllers/services/jobs. Do not introduce direct database access in Next.js.
- Keep Next.js data fetching centralized in typed API utilities when added. Do not scatter raw `fetch` calls across components.
- Keep `next.config.ts` standalone output unless the Docker production image is changed at the same time.
- Do not add new dependencies unless existing framework features are insufficient and the final report explains why.
- Docker image tags must stay pinned through root `.env*` image variables. Never use `latest` or broad floating tags.
- Do not commit real secrets. `.env`, `apps/api/.env`, and `apps/web/.env` are local/runtime files; update `.env.example` files for documented defaults only.

## OpenCode Agent Behavior

- Inspect relevant files before editing. For backend changes read routes, models, migrations, config, tests, and related docs. For frontend changes read the route/component, CSS, package scripts, and API env usage. For infra changes read Compose, Dockerfile, env examples, and scripts.
- Prefer minimal, focused changes. Do not rewrite unrelated scaffold files or broad-format the repo.
- Preserve existing architecture unless the user explicitly asks for a refactor.
- Separate implemented code from planned architecture in docs. Do not pretend that missing domain services already exist.
- Run the smallest relevant verification first, then broader checks before finalizing when available.
- Never hide failing tests, missing scripts, audit findings, or commands that could not run because containers are down.
- Final responses must report changed files, validation performed, failures or skipped checks, and remaining risks.

## Change Safety Rules

- Safe to change with normal review: docs, OpenCode config, focused Laravel app code, focused Next.js UI code, tests, examples, and non-secret env examples.
- Extra caution: `compose*.yaml`, Dockerfiles, entrypoints, Nginx/Caddy config, auth/Sanctum config, queue config, migrations, tenant scoping, AI provider code, production env examples, and `Makefile` targets.
- Do not change without explicit user instruction: real `.env` files with secrets, destructive reset behavior, database volume handling, public ports, TLS/domain settings, dependency major versions, generated lockfiles unrelated to the task, or old user changes in unrelated files.

## Definition of Done

A change is done only when it solves the requested problem, follows the repo conventions, includes relevant tests or a clear reason tests were not added, runs the available relevant Docker-based verification commands, and reports what changed, what was verified, and what risk remains.

<!-- SPECKIT START -->
For additional context about technologies to be used, project structure,
shell commands, and other important information, read the current plan:
`specs/001-policy-knowledge-base/plan.md`
<!-- SPECKIT END -->
