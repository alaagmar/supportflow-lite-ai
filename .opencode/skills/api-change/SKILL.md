---
name: api-change
description: Use when adding or changing Laravel JSON API routes, controllers, validation, resources, auth, or frontend API integration.
compatibility: opencode
---

# What I do

I implement API changes that follow Laravel conventions, Sanctum auth, tenant safety, role-based authorization, modular DDD boundaries, validation, resource responses, and Next.js consumption boundaries.

# When to use me

- Adding endpoints under `routes/api.php`.
- Creating controllers, Form Requests, API Resources, or Policies.
- Changing Sanctum-protected behavior.
- Wiring frontend API calls to Laravel endpoints.

# Required context

- `AGENTS.md` and `docs/engineering/api-design.md`.
- `apps/api/routes/api.php` and `bootstrap/app.php`.
- Relevant models, migrations, policies, requests, resources, tests.
- The owning bounded context from `AGENTS.md`: Identity and Workspace, Ticketing, Policy Knowledge Base, AI Processing, Audit and Analytics, or Billing Mock and Provider Settings.
- The required Owner/Admin/Agent/Viewer capabilities for the endpoint.
- `apps/web/.env.example` and frontend API usage if the UI is touched.

# Workflow

1. Inspect existing routes and auth setup.
2. Identify the owning module and whether the operation is Owner-only, Admin, Agent, or Viewer-readable.
3. Define request, authorization, persistence, response, and tests.
4. Add or update Form Request validation for non-trivial input.
5. Add or update Policy checks before tenant data access.
6. Put reusable business logic under `app/Domain/<Module>` only when the controller would otherwise own domain behavior.
7. Return API Resources for model data once domain models exist.
8. Add feature tests for auth, role authorization, tenant isolation, validation, and happy path.
9. Run focused API tests and route listing when possible.

# Project rules

- Workspace-scoped data must be authenticated and authorized.
- Role permissions follow `AGENTS.md`: Owner all, Admin operational management except owner-only settings, Agent assigned-ticket workflow, Viewer read-only.
- Controllers must not call AI providers directly.
- Next.js must not call the database directly.
- Use Docker-based Laravel commands only.

# Mistakes to avoid

- Route closures for real domain behavior.
- Trusting `workspace_id` from the client.
- Duplicating controllers or routes per role instead of using policies for the same domain operation.
- Returning raw Eloquent models for public domain endpoints.
- Building frontend screens against endpoints that do not exist.

# Completion checklist

- Routes are registered and protected correctly.
- Validation, role authorization, and tenant isolation are covered.
- Response shape is stable and documented if needed.
- Tests and route checks were run or blockers stated.
