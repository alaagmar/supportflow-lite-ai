# Data Model: Team Invitation and Member Management

## Entity: WorkspaceInvitation

Purpose: Tracks workspace-scoped invitation lifecycle from creation through acceptance/decline/revoke/expire.

Fields:
- id (primary key)
- workspace_id (foreign key -> workspaces.id, required, indexed)
- invited_email (required, normalized lowercase)
- invited_role (required, enum: owner|admin|agent|viewer; owner assignment remains owner-governed)
- status (required, enum: pending|accepted|declined|revoked|expired)
- invited_by_user_id (foreign key -> users.id, required)
- accepted_by_user_id (foreign key -> users.id, nullable)
- accepted_at (timestamp, nullable)
- declined_at (timestamp, nullable)
- revoked_at (timestamp, nullable)
- expires_at (timestamp, required; default 7 days from creation)
- created_at / updated_at

Validation and constraints:
- Unique active invitation per workspace+invited_email (enforced at application layer and/or partial unique index for `pending`).
- Acceptance allowed only when authenticated user email equals invited_email.
- Transitions allowed:
  - pending -> accepted
  - pending -> declined
  - pending -> revoked
  - pending -> expired
- Terminal states are immutable except for read operations.

## Entity: WorkspaceMember (existing)

Purpose: Represents active user membership and role inside a workspace.

Relevant existing fields:
- id
- workspace_id (foreign key)
- user_id (foreign key)
- role (owner|admin|agent|viewer)
- created_at / updated_at

New behavior constraints:
- Admin can manage non-owner members only.
- Owner can manage all members except invalid operations (e.g., removing or demoting the last owner).
- Membership creation from invitations occurs only for valid pending invites.

## Entity: MembershipChangeEvent

Purpose: Captures operational trace of invitation/member mutations for later audit domain integration.

Event payload fields (logical contract):
- workspace_id
- actor_user_id
- target_user_id or invited_email
- action (invite_created, invite_revoked, invite_accepted, invite_declined, role_changed, member_removed)
- outcome (success|denied|failed)
- occurred_at

Storage note:
- Can be emitted into existing structured logs now and migrated to audit tables in the audit module.

## Relationships

- Workspace 1..* WorkspaceInvitation
- Workspace 1..* WorkspaceMember
- User 1..* WorkspaceInvitation (as inviter)
- User 0..* WorkspaceInvitation (as accepter)
- User 1..* WorkspaceMember

## State Transitions

Invitation state machine:
- pending (initial)
- accepted (membership created)
- declined
- revoked (by owner/admin with permission)
- expired (time-based transition)

Member governance transitions:
- role change: viewer<->agent<->admin by allowed actor constraints
- remove member: active -> removed (row deleted or inactive per implementation choice)
- owner safeguards: last owner cannot be removed or demoted
