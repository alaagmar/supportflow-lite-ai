---
description: Create a behavior-preserving refactor plan for this Laravel/Next/Docker monorepo without editing files.
agent: plan
---

Create a refactor plan only. Do not edit files.

Use `AGENTS.md` and `docs/engineering/refactoring.md`.

Include this context:

!`git status --short`
!`git diff --stat`

The plan must state:

- Current behavior to preserve.
- Files and boundaries affected.
- Risks to Docker topology, env names, routes, schema, lockfiles, and tests.
- Minimal sequence of safe steps.
- Verification commands to run through Docker.
- What not to change without explicit approval.

Keep the plan project-specific. Do not propose generic cleanup or architecture not already supported by this repository.
