# Data Model: Audit and Analytics Workspace Insights

## Entity: AuditLog (new)

Purpose: Tenant-scoped immutable record of domain actions for investigation and compliance-style traceability.

Fields:
- id (primary key)
- workspace_id (foreign key -> workspaces.id, required, indexed)
- user_id (foreign key -> users.id, nullable for system-triggered actions, indexed)
- entity_type (required; e.g., ticket, policy_document, workspace_invitation, workspace_member, ai_run)
- entity_id (required; target aggregate identifier)
- action (required; canonical event name, indexed)
- metadata_json (required; safe IDs/status context only)
- created_at (required, indexed)

Validation and constraints:
- `workspace_id` is mandatory for every record.
- Metadata must not include raw secrets, auth tokens, or full customer message payloads.
- Action names must follow canonical dot-notation (e.g., `ticket.created`).
- Record retention target is 12 months.

State behavior:
- Append-only lifecycle (no update/delete in MVP user flow).

## Entity: AuditTimelineQuery (derived/read model)

Purpose: User-selected filter contract for timeline retrieval.

Fields:
- workspace_id (required)
- actor_user_id (optional)
- action_family_or_name (optional)
- start_at (optional)
- end_at (optional)
- ticket_id (optional for ticket-specific timeline)
- page / per_page (required for pagination)

Validation and constraints:
- `start_at <= end_at` when both are provided.
- Pagination required for responses beyond single-page defaults.
- Query execution must be scoped to authorized workspace membership.

## Entity: WorkspaceAnalyticsSummary (derived/read model)

Purpose: Workspace-level operational summary over a date window.

Fields:
- workspace_id (required)
- window_start_at (required)
- window_end_at (required)
- total_tickets (count)
- tickets_needing_review (count)
- tickets_resolved (count)
- ai_runs_completed (count)
- ai_runs_failed_or_fallback (count)
- last_updated_at (timestamp)

Validation and constraints:
- Summary values are derived only from records visible within the selected workspace and date window.
- Empty windows return zero-values instead of errors.
- Access is read-only for owner/admin/viewer roles.

## Relationship Notes

- One Workspace has many AuditLog records.
- One User may be actor for many AuditLog records.
- One Ticket may map to many AuditLog records and appears in ticket-specific timeline queries.
- One WorkspaceAnalyticsSummary is generated per request window and is not an MVP persistent write model.
