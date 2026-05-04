---
name: module-architecture
description: Use when designing or changing role-aware domain modules, DDD boundaries, or cross-module Laravel/Next.js feature slices.
compatibility: opencode
---

# What I do

I design and check SupportFlow feature slices so they stay modular, role-aware, tenant-safe, and aligned with lightweight DDD in Laravel 12 and Next.js 15.

# When to use me

- Starting a new bounded context or feature slice.
- Adding role-specific capabilities for Owner, Admin, Agent, or Viewer.
- Deciding where Laravel domain behavior, policies, jobs, services, resources, or tests belong.
- Deciding where Next.js App Router pages, feature components, and typed API helpers belong.
- Reviewing whether a change should be module-owned or shared.

# Required context

- `AGENTS.md` Product Roles and Modular DDD Boundaries.
- `docs/copy1.md` for source product requirements when clarifying role intent.
- Relevant `docs/engineering/*` files for backend, frontend, database, API, testing, security, and architecture standards.
- Existing code in `apps/api` and `apps/web` for the affected module.

# Role Matrix

- Owner: full workspace control, including workspace settings, billing mock/settings, team members, AI provider settings, usage dashboard, and owner-only destructive/settings actions.
- Admin: operational management for tickets, policies, AI processing, assignments, workflow rules, and agent invites, plus log viewing. No workspace deletion or owner-only billing/provider settings.
- Agent: assigned-ticket workflow, AI draft review/edit/approve/reject, ticket processing where policy allows, assignment where policy allows, and ticket resolution. No policy, team, provider, or owner-only settings management.
- Viewer: read-only access to tickets, analytics, and audit logs. No mutation, approval, processing, assignment, or deletion.

# Module Boundaries

- Identity and Workspace owns users, workspaces, membership, role assignment, switching, and workspace settings.
- Ticketing owns tickets, ticket messages, comments, assignments, statuses, queues, and review actions.
- Policy Knowledge Base owns policy documents, chunks, ingestion, retrieval, and evidence metadata.
- AI Processing owns provider interfaces, Mistral/mock implementations, prompt templates, JSON validation, queued pipeline jobs, AI runs, and ticket AI output.
- Audit and Analytics owns audit logs, AI timelines, dashboard metrics, usage summaries, and read models when they exist.
- Billing Mock and Provider Settings owns owner-only billing placeholder state and AI provider configuration screens.

# Laravel 12 Guidance

- Keep routes, controllers, requests, resources, policies, models, jobs, migrations, factories, and seeders in conventional Laravel locations unless there is a concrete reason to do otherwise.
- Use `app/Domain/<Module>` for reusable business behavior only after it exists: actions, services, DTOs, value objects, enums, prompt builders, retrieval logic, or provider contracts.
- Keep controllers thin: validate, authorize, delegate to domain behavior or Eloquent relationships, then return resources.
- Use policies for role differences. Role-prefixed owner/admin/staff controllers may exist as thin portal adapters, but do not duplicate models, tables, repositories, use cases, resources, or business rules per role for the same domain operation.
- Prefer single-action invokable API controllers. Shared module behavior belongs in `app/Domain/<Module>` once real behavior exists.
- Use `App\Http\Responses\ApiResponse` and API Resources for new successful JSON responses where practical. Keep custom app exceptions under `app/Exceptions`.
- Scope tenant data through workspace membership and module relationships before model access. Do not fetch by global ID then authorize afterward.

# Current Backend Norms

- API route groups are role-prefixed: `/api/owner`, `/api/admin`, and `/api/staff`.
- Owner portal accepts owner memberships only; Admin portal accepts owner/admin memberships; Staff portal accepts owner/admin/agent/viewer memberships.
- Workspace creation is owner-only and must create the owner membership in the same transaction.
- Workspace reads should be scoped through authenticated user memberships and avoid leaking non-member workspace existence.
- Identity and Workspace already use shared domain contracts, repositories, use cases, Form Requests, API Resources, policies, and feature tests. Follow that shape for adjacent backend slices unless a smaller conventional Laravel path is clearly enough.

# Next.js 15 Guidance

- Keep route files under `src/app` and organize dashboard routes by workflow/module, not by backend implementation class.
- Use Server Components by default; use Client Components for forms, browser APIs, local interactivity, optimistic interaction, or accessible dynamic controls.
- Extract feature code only when reuse or complexity justifies it, for example `src/features/tickets` or `src/features/policies`.
- Keep API calls typed and centralized by module once real data fetching is added.
- Render role-specific actions as UX affordances, but rely on Laravel policies for enforcement.

# Mistakes to avoid

- Creating Owner/Admin/Agent/Viewer copies of the same domain module instead of portal adapters plus one module with policy-controlled capabilities.
- Adding empty DDD folders, repositories, managers, or abstractions before behavior needs them.
- Letting Next.js own authorization, persistence, queue orchestration, AI provider calls, or audit writes.
- Mixing module work with unrelated Docker, dependency, env, or formatting changes.
- Treating `docs/copy1.md` as overriding current PostgreSQL, Redis, Docker-only, or no-new-dependency project rules.

# Completion checklist

- The owning module is named.
- Role capabilities and denial cases are explicit.
- Backend authorization is policy-backed and tenant-scoped.
- Frontend role behavior is mirrored as UX, not trusted as security.
- Tests or verification cover the highest-risk role and tenant paths.
