# Clean Code Standards

## Purpose

Set concrete maintainability rules for this Laravel/Next/Docker monorepo. This is not generic style advice; it exists to protect the project boundaries and scaffold clarity.

## Rules

- Keep changes small and local to the requested feature.
- Prefer framework conventions over custom abstractions.
- Use names from the domain docs: workspace, ticket, policy document, policy chunk, AI run, ticket AI output, audit log.
- Do not add compatibility layers for behavior that has not shipped.
- Do not preserve dead generated code in new domain code just because Laravel or Next generated it elsewhere.
- Add comments only when the reason is not obvious, such as retry behavior, tenant-safety constraints, or AI fallback decisions.
- Keep public API response shapes consistent once resources are added.

## Preferred Patterns

- Laravel: one controller action should validate, authorize, delegate when needed, and return a resource.
- Laravel: services should represent real boundaries such as AI provider integration or policy retrieval, not generic managers.
- Next.js: keep components simple and colocated until reuse justifies extraction.
- Tests: name tests after behavior, not implementation details.
- Docs: update the closest README or engineering doc when commands or conventions change.

## Forbidden Patterns

- Generic `helpers.php`, `utils.ts`, or `Manager` classes without a clear owner and use case.
- Multiple new abstractions for one endpoint or one component.
- Mixing unrelated cleanup with feature work.
- Silent catch blocks around queue/AI/database failures.
- Leaving TODO comments for required correctness work in production paths.

## Examples From This Repository

- `apps/web/src/app/page.tsx` is small enough to stay in one file today. Extract only when adding real reused UI.
- `apps/api/routes/api.php` should move beyond closures for real domain endpoints, but the scaffold `/user` closure can remain until auth/user endpoint work happens.
- `infra/scripts/*.sh` are short POSIX scripts. Keep future scripts direct and readable.

## Common Mistakes To Avoid

- Adding a repository/service layer for every model before there is business logic.
- Adding frontend state management before there is shared client state.
- Hiding missing tests behind generated example tests.
- Renaming domain terms away from the architecture docs.

## Verification Checklist

- Change set is focused and explainable.
- New names match existing domain language.
- No unnecessary dependency or abstraction was added.
- Relevant docs/tests were updated.
