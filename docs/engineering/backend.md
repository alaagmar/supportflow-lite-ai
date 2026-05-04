# Backend Standards

## Purpose

Define Laravel-specific rules for `apps/api` based on the current Laravel 12 scaffold and the accepted SupportFlow architecture docs.

## Rules

- Keep backend code inside `apps/api`.
- Use Laravel 12 conventions: routes in `routes/api.php`, controllers in `app/Http/Controllers`, Form Requests in `app/Http/Requests`, API Resources in `app/Http/Resources`, Jobs in `app/Jobs`, Policies in `app/Policies`, Models in `app/Models`.
- Keep Sanctum for API authentication. Do not replace it with another auth system without explicit approval.
- Use PostgreSQL-compatible Laravel migrations. Avoid MySQL-specific column types, raw SQL, and SQLite-only assumptions.
- Use Redis queues for async work. The queue worker is the Docker `worker` service.
- Add `declare(strict_types=1);` to new PHP domain files where practical. Do not churn generated Laravel files only to add it.
- Type method parameters and return values in new PHP code.
- Use dependency injection or container bindings for services instead of facades for business dependencies where practical.

## Preferred Patterns

- Validation: create Form Request classes for non-trivial API input instead of validating inline in controllers.
- Authorization: create Policies for domain models and call `$this->authorize(...)` before reads or writes.
- Serialization: return API Resources for model responses rather than raw model arrays.
- Queue jobs: make jobs idempotent, set explicit `$tries`, `$timeout`, and `$backoff`, and implement `failed(Throwable $e)` for state cleanup.
- AI provider calls: use a provider interface and service classes under `app/Services/Ai` when that layer is implemented.
- Logging: use Laravel logging with structured context arrays. Do not log request bodies, API keys, tokens, or full AI prompts if they may contain customer data.

## Forbidden Patterns

- Controller methods that process AI synchronously.
- Direct HTTP calls to Mistral from controllers, routes, seeders, or React-facing endpoints.
- Route closures for real domain endpoints beyond scaffold/health checks.
- Using global tenant model lookups without workspace scoping once tenant models exist.
- Returning Eloquent models directly for public API endpoints once resources exist.
- Running Laravel commands on the host in docs, Makefile targets, or instructions.

## Examples From This Repository

- `apps/api/routes/api.php` includes role-prefixed auth/workspace routes plus workspace-scoped ticket routes for owner/admin/staff portals.
- `apps/api/app/Models/User.php` uses `HasApiTokens` and generated Laravel casts.
- `apps/api/phpunit.xml` targets the Docker PostgreSQL service and uses sync queues for tests.
- `apps/api/config/queue.php` still has generated fallback defaults in some keys. Do not rely on those defaults; project env files set Redis/PostgreSQL explicitly.

## Common Mistakes To Avoid

- Assuming tests can run against SQLite. The project direction is PostgreSQL-only.
- Adding `laravel/sail` workflow instructions even though this repo uses custom Docker Compose files and Make targets.
- Adding package scripts that duplicate Docker-owned startup behavior.
- Saving unvalidated AI JSON directly to models.

## Verification Checklist

- For PHP syntax: run relevant Laravel tests through Docker when the stack is running.
- For style: `docker compose -f compose.yaml -f compose.dev.yaml exec api ./vendor/bin/pint`.
- For package integrity: `docker compose -f compose.yaml -f compose.dev.yaml exec api composer validate --strict`.
- For routes: `docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan route:list`.
- For migrations: `make migrate` or focused migration tests, never host-local Artisan.
