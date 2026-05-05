# Implementation Plan: [FEATURE]

**Branch**: `[###-feature-name]` | **Date**: [DATE] | **Spec**: [link]
**Input**: Feature specification from `/specs/[###-feature-name]/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

[Describe the user value, the owning bounded context, and the smallest technical
approach that satisfies the feature without violating the constitution.]

## Technical Context

<!--
  ACTION REQUIRED: Replace the content in this section with the technical details
  for the project. The structure here is presented in advisory capacity to guide
  the iteration process.
-->

**Backend Changes**: [Laravel 12 controllers/requests/resources/jobs/models/routes or N/A]  
**Frontend Changes**: [Next.js 15 App Router pages/components/features/lib or N/A]  
**Infrastructure Changes**: [Compose, Dockerfile, entrypoint, env example, proxy config or N/A]  
**Data/Storage**: [PostgreSQL tables/models, Redis usage, or N/A]  
**Testing/Verification**: [e.g., make test-api, web lint/build, compose config --quiet]  
**Target Platform**: Docker Compose dev and prod stack  
**Project Type**: Docker-first Laravel + Next.js monorepo  
**Performance Goals**: [feature-specific latency, throughput, or N/A]  
**Constraints**: Docker-only workflow; Laravel owns data/auth/AI; Next.js consumes APIs; PostgreSQL-only; pinned images; no secret commits  
**Scale/Scope**: [workspace/module scope, expected ticket volume, or N/A]

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [ ] All execution, test, build, migration, and setup steps use `make` or
      `docker compose ... exec`; no host-local Composer, npm, Artisan, Next.js,
      or test workflow is introduced.
- [ ] Changed files stay within their owning layer and bounded context, with no
      duplicated business logic across role-prefixed entry points.
- [ ] Tenant-owned data access, role authorization, and `workspace_id` handling
      are specified and testable for affected backend work.
- [ ] AI or long-running processing remains asynchronous via Laravel jobs, and
      external AI responses are validated before persistence or display.
- [ ] Planned verification matches the change type: backend tests for backend
      behavior, frontend lint/build for frontend work, and Compose or Docker
      validation for infrastructure changes.
- [ ] Any new dependency, migration, auth change, or infrastructure risk is
      justified in Complexity Tracking.

## Project Structure

### Documentation (this feature)

```text
specs/[###-feature]/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)

```text
apps/
├── api/
│   ├── app/
│   │   ├── Domain/
│   │   ├── Http/
│   │   ├── Jobs/
│   │   ├── Models/
│   │   └── Policies/
│   ├── database/
│   ├── routes/
│   └── tests/
│       ├── Feature/
│       └── Unit/
└── web/
    └── src/
        ├── app/
        ├── components/
        ├── features/
        └── lib/
infra/
├── caddy/
├── docker/
├── nginx/
├── php/
└── scripts/
docs/
└── engineering/
.specify/
└── templates/
```

**Structure Decision**: [List the real directories this feature touches and
explain why each one owns that work.]

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| [e.g., 4th project] | [current need] | [why 3 projects insufficient] |
| [e.g., Repository pattern] | [specific problem] | [why direct DB access insufficient] |
