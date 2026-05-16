# Research: Audit and Analytics Workspace Insights

## Decision 1: Audit event persistence model

- Decision: Add a dedicated `audit_logs` table scoped by `workspace_id` with indexed action and created-time fields.
- Rationale: Timeline and ticket-level investigation need durable, queryable, tenant-scoped records rather than ephemeral logs.
- Alternatives considered:
  - Use application log files as source of truth (poor queryability, weak tenant filtering, unstable schema).
  - Delay persistence until a future analytics warehouse (blocks current feature value and acceptance criteria).

## Decision 2: Access matrix for read endpoints

- Decision: Allow read-only audit/analytics access to owner, admin, and viewer roles; deny agent; return non-disclosing not-found for non-members.
- Rationale: Matches product role matrix and clarified spec behavior while preserving tenant privacy.
- Alternatives considered:
  - Owner/admin only (conflicts with viewer responsibilities).
  - Allow agent read access (expands policy surface and conflicts with clarified scope).

## Decision 3: Metadata safety boundary

- Decision: Persist only safe operational context in audit metadata (IDs, statuses, transition context), excluding full request bodies, raw customer content, and secret values.
- Rationale: Preserves incident traceability while reducing sensitive data exposure risk.
- Alternatives considered:
  - Store full payload snapshots (higher security/privacy risk and unnecessary for MVP investigations).
  - Store action-only records with no metadata (insufficient forensic context for troubleshooting).

## Decision 4: Analytics summary strategy

- Decision: Compute analytics from existing workspace ticket and AI outcome data over a selectable date window; no new external store in MVP.
- Rationale: Delivers actionable dashboard value with minimal architecture change and no new infrastructure dependencies.
- Alternatives considered:
  - Introduce separate analytics pipeline/warehouse now (high complexity for current scope).
  - Precompute only nightly snapshots (stale insights and weak UX for date-window changes).

## Decision 5: Retention and MVP scope controls

- Decision: Keep audit/analytics source records for 12 months and keep feature view-only (no export) for MVP.
- Rationale: Satisfies clarified governance and scope limits while avoiding broad data-export and compliance expansion in this phase.
- Alternatives considered:
  - Indefinite retention (unbounded storage/privacy obligations).
  - Add CSV/JSON export now (larger security/compliance/test surface not required for MVP).
