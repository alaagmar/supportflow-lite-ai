# Refactoring Standards

## Purpose

Keep refactors safe in a scaffolded monorepo with many pending features and some aspirational legacy agent docs.

## Rules

- Refactor only code required by the user's request or clearly necessary to unblock it.
- Separate behavior changes from mechanical moves when possible.
- Do not rename public routes, env variables, Compose services, Make targets, Docker volumes, or database columns without explicit migration/compatibility planning.
- Do not remove scaffold files simply because they are unused unless the user asks for cleanup.
- Preserve generated lockfiles unless dependency changes require updating them.
- Prefer one small extraction over new abstraction layers.

## Preferred Patterns

- Start with a refactor plan for cross-cutting changes.
- Add or run tests before refactoring behavior when tests exist.
- Keep Docker service names stable: `api`, `api-nginx`, `worker`, `scheduler`, `web`, `postgres`, `redis`, `caddy`, `mailpit`.
- Update docs when refactoring changes commands, topology, env variables, or directory responsibilities.

## Forbidden Patterns

- Large-scale formatting mixed with functional changes.
- Moving Laravel code outside `apps/api` or Next code outside `apps/web`.
- Replacing Docker Compose with Sail, Vercel-only workflow, or host-local workflow.
- Changing `apps/web` package manager away from npm while `package-lock.json` is present.
- Removing `output: "standalone"` without production Dockerfile changes.

## Examples From This Repository

- A safe refactor could extract a repeated frontend card component from `src/app/page.tsx` if it becomes reused.
- A risky refactor would rename Compose services because env values and Docker networking depend on those names.
- A risky cleanup would delete generated Laravel config without verifying framework expectations.

## Common Mistakes To Avoid

- Treating docs-only target architecture as implemented code and moving files that do not exist.
- Breaking first-start dependency installation in `entrypoint.dev.sh` or `compose.dev.yaml`.
- Updating `.env.example` but not root README command docs.
- Refactoring tests that are only examples without adding real coverage.

## Verification Checklist

- Show before/after scope in the final report.
- Run the smallest relevant test or lint command.
- Run `docker compose -f compose.yaml -f compose.dev.yaml config --quiet` for infra refactors.
- Confirm no unrelated files were reformatted or rewritten.
