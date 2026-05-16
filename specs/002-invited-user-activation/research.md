# Research: Invited User Account Activation

## Decision 1: Activation token storage model

- Decision: Use a dedicated `workspace_invitation_activation_tokens` table linked to `workspace_invitations` with hashed token, expiry, used_at, and resend-window metadata.
- Rationale: The flow is invitation-scoped (not generic password reset), needs "latest-link only" semantics, and requires per-invitation resend throttling (3 per 24h).
- Alternatives considered:
  - Reuse `password_reset_tokens` directly (cannot cleanly model invitation FK, resend counters, or token invalidation semantics).
  - Stateless signed URL only (harder to revoke/rotate and enforce resend limits reliably).

## Decision 2: Account provisioning timing for new invitees

- Decision: For invited emails without an account, do not pre-create a user record; create the user only on successful activation password submission.
- Rationale: Avoids placeholder accounts with random credentials and keeps account creation coupled to verified email-link possession.
- Alternatives considered:
  - Create placeholder user at invite creation (adds cleanup complexity and potential dormant accounts).
  - Block invites to unknown emails (conflicts with team onboarding goal).

## Decision 3: Existing active account behavior

- Decision: Skip activation email for invites targeting an already active account; user signs in with existing credentials and accepts invitation normally.
- Rationale: Prevents unnecessary credential reset-like flows and aligns with clarified requirement FR-001b.
- Alternatives considered:
  - Force activation/password setup for existing accounts (unnecessary friction and security confusion).
  - Block invite for existing accounts (breaks expected cross-workspace onboarding for existing users).

## Decision 4: Email dispatch and failure handling

- Decision: Dispatch activation email asynchronously through existing queue/mail infrastructure, and record lifecycle events for sent, failed, expired, and completed states.
- Rationale: Preserves responsive invite creation UX and aligns with Docker-first async processing patterns already used in the project.
- Alternatives considered:
  - Synchronous email send during invitation creation (fragile and couples API success to provider latency).
  - No delivery event recording (violates FR-009 traceability intent).

## Decision 5: Recovery and resend policy

- Decision: Provide a public resend endpoint keyed by invitation/email context with a strict limit of 3 resends per 24-hour window per invitation; each resend invalidates prior active token and issues a new one.
- Rationale: Meets clarified anti-abuse requirement while preserving recoverability for expired/invalid links.
- Alternatives considered:
  - Unlimited resend (abuse and mail flood risk).
  - One resend per day (too restrictive for real onboarding failures).
