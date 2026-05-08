# Research: Team Invitation and Member Management

## Decision 1: Invitation acceptance identity rule

- Decision: Only the authenticated account whose email exactly matches the invited email can accept.
- Rationale: Prevents forwarded-link misuse and keeps membership grants bound to intended recipients.
- Alternatives considered:
  - Any authenticated account can accept with confirmation (higher account-takeover risk).
  - Domain-based acceptance (insufficient precision for multi-tenant security).

## Decision 2: Invitation expiration policy

- Decision: Pending invitations expire 7 days after creation.
- Rationale: Balances operational usability with security by limiting stale pending access grants.
- Alternatives considered:
  - 3 days (more secure but causes avoidable re-invite churn).
  - 14 days (lower churn but larger stale-link risk window).

## Decision 3: Admin scope for member governance

- Decision: Admins can invite/revoke and manage non-owner members (role updates/removal); owner-only actions remain protected.
- Rationale: Matches existing role matrix and reduces operational bottlenecks while preserving owner safeguards.
- Alternatives considered:
  - Owner-only member governance (safer but too restrictive for day-to-day operations).
  - Admin full parity with owner (violates owner-only governance intent).

## Decision 4: API shape and role-prefixed entry points

- Decision: Add role-prefixed routes under `/api/owner`, `/api/admin`, and `/api/staff` as thin portal entry points that delegate to shared domain behavior.
- Rationale: Preserves existing API consistency and avoids role-silo duplication.
- Alternatives considered:
  - Single unprefixed route tree (would diverge from established project contract).
  - Fully duplicated role-specific implementations (higher defect and maintenance risk).

## Decision 5: Invitation delivery behavior

- Decision: Invitation creation is successful when invitation persistence succeeds; notification delivery is best-effort and asynchronous.
- Rationale: Avoids blocking user flows on downstream delivery variability while keeping invitation state authoritative.
- Alternatives considered:
  - Require synchronous delivery before success (fragile UX and coupling to mail delivery availability).
  - No delivery integration at all (hurts adoption and operational usability).
