# Data Model: Invited User Account Activation

## Entity: WorkspaceInvitation (existing)

Purpose: Invitation lifecycle source of truth; activation is derived from pending invitations.

Relevant fields used by activation flow:
- id
- workspace_id
- invited_email
- invited_role
- status (pending, accepted, declined, revoked, expired)
- expires_at

Activation-related constraints:
- Activation is allowed only while invitation is `pending` and not expired/revoked/declined.
- Membership is not created during activation; membership is created only by invitation acceptance.

## Entity: WorkspaceInvitationActivationToken (new)

Purpose: Secure, invitation-bound token for first-time password setup.

Fields:
- id (primary key)
- workspace_invitation_id (foreign key -> workspace_invitations.id, required, indexed)
- invited_email (required, normalized lowercase, indexed)
- token_hash (required, unique)
- expires_at (required; 7 days from issue)
- used_at (timestamp, nullable)
- issued_at (timestamp, required)
- invalidated_at (timestamp, nullable)
- resend_count_window (integer, default 0)
- resend_window_started_at (timestamp, nullable)
- last_sent_at (timestamp, nullable)
- created_at / updated_at

Validation and constraints:
- Only one active token per invitation at a time.
- A token is active when `used_at` and `invalidated_at` are null and `expires_at > now`.
- Resend cap: maximum 3 sends per 24-hour rolling window per invitation.
- On resend, current active token is invalidated and a new token is issued.

State transitions:
- issued -> used (password successfully set)
- issued -> invalidated (resend or manual invalidation)
- issued -> expired (time based)

## Entity: User (existing)

Purpose: Auth identity for sign-in.

Activation-related behavior:
- Existing active account with invited email bypasses activation email and uses normal sign-in + invitation acceptance.
- For invited email without existing account, user record is created only at successful activation completion.
- Pre-activation sign-in for invitee-without-account is denied because credentials do not exist yet.

## Operational Event Contract (log-backed)

Purpose: Satisfy FR-009 lifecycle traceability until audit module expansion.

Event names and required context:
- `invitation_activation.email_sent`
- `invitation_activation.email_failed`
- `invitation_activation.completed`
- `invitation_activation.expired`
- `invitation_activation.denied`

Common context fields:
- workspace_id
- invitation_id
- invited_email
- actor_type (system or user)
- outcome (success, failed, denied)
- occurred_at
