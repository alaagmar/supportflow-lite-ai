---
name: safe-refactor
description: Use when changing structure or names without intending to change behavior in this Docker-based Laravel and Next.js monorepo.
compatibility: opencode
---

# What I do

I plan and execute behavior-preserving refactors while protecting Docker topology, app boundaries, role behavior, modular DDD boundaries, env names, Make targets, and framework conventions.

# When to use me

- Moving or renaming Laravel classes, Next.js components, docs, or infra files.
- Reducing duplication in a feature path.
- Splitting large files after functionality is already working.
- Cleaning generated scaffold code after the user asks for cleanup.

# Required context

- `AGENTS.md`.
- `docs/engineering/refactoring.md`.
- The affected bounded context and Owner/Admin/Agent/Viewer behavior from `AGENTS.md`.
- Relevant app files and tests.
- `Makefile`, Compose files, and Dockerfiles if runtime behavior could change.
- Package files and lockfiles if dependencies or scripts are touched.

# Workflow

1. Define the behavior that must remain unchanged.
2. Identify the owning module and role behavior that must remain unchanged.
3. Inspect tests or add characterization tests if behavior is important and untested.
4. Choose the smallest refactor that solves the problem.
5. Avoid changing public routes, env variables, Docker service names, Make targets, and schema names unless explicitly requested.
6. Apply focused edits.
7. Run the smallest relevant verification, then broader checks if available.
8. Report changed files and any behavior that might have changed.

# Project rules

- Do not move Laravel code outside `apps/api` or Next code outside `apps/web`.
- Preserve `next.config.ts` standalone output unless production Docker changes too.
- Preserve npm for `apps/web` because `package-lock.json` exists.
- Preserve Docker-only commands and pinned image tags.
- Do not refactor around planned domain classes that are not implemented yet.
- Do not split code by role when the correct boundary is a domain module plus policy checks.

# Mistakes to avoid

- Mixing broad formatting with a refactor.
- Renaming Compose services used for internal networking.
- Updating generated lockfiles without dependency changes.
- Removing scaffold files without verifying framework expectations.
- Adding abstractions before the second real use case.
- Moving framework-owned Laravel files into custom module folders without preserving conventions.

# Completion checklist

- Scope stayed focused.
- Behavior changes, if any, are explicitly called out.
- Role permissions and module ownership were preserved.
- Relevant tests/lint/build/config checks were run or honestly skipped.
- Docs were updated if commands, env names, or file responsibilities changed.
