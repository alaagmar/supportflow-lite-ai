# API Design Standards

## Purpose

Define how JSON API endpoints should be added to the Laravel API and consumed by the Next.js frontend.

## Rules

- API endpoints belong in `apps/api/routes/api.php`.
- Use Sanctum middleware for authenticated endpoints.
- Workspace-scoped endpoints must include workspace context and verify membership before accessing tenant data.
- Request validation belongs in Form Requests for non-trivial payloads.
- Authorization belongs in Policies, not inline role checks scattered through controllers.
- Responses should be JSON objects using Laravel API Resources for model data.
- Validation errors should use Laravel's standard `{ "message": "...", "errors": { ... } }` shape.

## Preferred Patterns

- Keep route groups explicit: auth middleware, workspace prefix, then resources.
- Return `201` for creates, `200` for reads/updates, and `204` for deletes where appropriate.
- Paginate list endpoints when a table can grow.
- Use stable status strings from `docs/architecture/database-design.md` for ticket and AI run states.
- Use API tests for each endpoint that cover auth, authorization, validation, and happy path.

## Forbidden Patterns

- Unauthenticated tenant data endpoints.
- Returning raw models or internal exception traces from public endpoints.
- Next.js components constructing many endpoint strings manually across the app.
- API routes that process AI inline before responding.
- Controllers that trust `workspace_id` from request input without verifying membership.

## Examples From This Repository

- Existing `GET /api/user` is Sanctum-protected in `routes/api.php`.
- Existing `GET /sanctum/csrf-cookie` is provided by Sanctum after `statefulApi()` was enabled.
- Domain endpoints for workspaces, tickets, policy documents, AI runs, and audit logs are not implemented yet.

## Common Mistakes To Avoid

- Building frontend screens against imagined endpoints without adding API tests.
- Returning AI provider raw output directly to the frontend before validation and persistence.
- Mixing HTML web routes and JSON API routes for domain features.
- Skipping authorization because the route is already authenticated.

## Verification Checklist

- Run focused feature tests for changed endpoints.
- Run `docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan route:list` when routes change.
- Confirm frontend env values point to `http://localhost:8080` for browser dev and `http://api-nginx` for server-side container calls.
