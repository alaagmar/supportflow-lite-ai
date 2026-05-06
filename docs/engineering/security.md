# Security Standards

## Purpose

Protect tenant data, credentials, AI inputs/outputs, and production runtime configuration.

## Rules

- Never commit real secrets. Root `.env`, `apps/api/.env`, and `apps/web/.env` are local/runtime files.
- Only document safe placeholder values in `.env.example` files.
- Keep PostgreSQL and Redis on the private Docker network with no public port mappings.
- Keep API access through Caddy/Nginx rather than exposing PHP-FPM directly.
- Use Sanctum for first-party SPA auth and keep `SANCTUM_STATEFUL_DOMAINS` aligned with frontend URLs.
- Workspace membership must be checked before reading or mutating tenant data.
- Do not log API tokens, session cookies, passwords, AI provider keys, raw customer emails in bulk, or full customer ticket bodies unless explicitly needed and redacted.
- Validate every external AI JSON response before persistence or display.
- File uploads, when added, must validate MIME type, size, ownership, storage path, and tenant scope.

## Preferred Patterns

- Use Form Requests for validation and Policies for authorization.
- Use Laravel's password hashing and hidden model attributes as in the generated `User` model.
- Use structured logs with safe IDs and status fields rather than raw payload dumps.
- Keep Caddy domains and production credentials environment-specific.
- Review `.gitignore` before adding files that might contain secrets or generated state.

## Forbidden Patterns

- `NEXT_PUBLIC_*` secrets. Anything with `NEXT_PUBLIC_` is browser-visible.
- Public Docker port mappings for PostgreSQL, Redis, API PHP-FPM, worker, or scheduler.
- Direct AI-to-production actions without human approval for risky ticket actions.
- Trusting `workspace_id` from client input as proof of access.
- Committing `auth.json`, private keys, `.pem`, app keys, database passwords, or AI provider API keys (for example `QWEN_API_KEY`).

## Examples From This Repository

- `.gitignore` excludes root `.env` and app env files.
- `compose.yaml` places `postgres` and `redis` only on the private network.
- `infra/nginx/api.*.conf` blocks hidden files and routes PHP through FPM.
- `apps/api/.env.example` currently has safe local defaults and an empty `QWEN_API_KEY`.

## Common Mistakes To Avoid

- Adding a frontend env var for `QWEN_API_KEY` or server-only URLs with a `NEXT_PUBLIC_` prefix.
- Logging `$request->all()` in controllers that handle tickets or auth.
- Disabling authorization because a route is internal to the UI.
- Adding Mailpit to production without a deliberate reason.

## Verification Checklist

- Search changed files for secrets and unsafe env values.
- Check Docker network and port exposure after Compose changes.
- Confirm auth middleware and policy checks for protected API endpoints.
- Confirm logs and errors do not expose sensitive payloads.
- Run `docker compose -f compose.yaml -f compose.dev.yaml config --quiet` after infra changes.
