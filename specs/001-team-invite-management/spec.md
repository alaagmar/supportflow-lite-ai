# Feature Specification: Team Invitation and Member Management

**Feature Branch**: `001-team-invite-management`  
**Created**: 2026-05-08  
**Status**: Draft  
**Input**: User description: "Team invitation and member management"

## Clarifications

### Session 2026-05-08

- Q: What member-management actions can admins perform? -> A: Admins can invite, revoke invites, change roles for non-owners, and remove non-owners.
- Q: What is the invitation expiration window? -> A: Invitations expire after 7 days.
- Q: Who can accept an invitation? -> A: Only an account with the exact invited email can accept.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Invite Team Members (Priority: P1)

Workspace owners and admins can invite new teammates to join a workspace with a
specific role so team access can be granted without manual account administration.

**Why this priority**: Team onboarding is the first blocker for multi-user
operation and is required before role-based collaboration can happen.

**Independent Test**: From an owner or admin account, send an invitation to an
email with a selected role and verify the invitation appears as pending and is
deliverable to the recipient.

**Acceptance Scenarios**:

1. **Given** an owner is in a workspace, **When** they invite a new user email
   as agent, **Then** the invitation is created with pending status and the
   invited role.
2. **Given** an admin is in a workspace, **When** they invite a new user email
   as viewer, **Then** the invitation is created successfully.
3. **Given** an owner or admin invites an email that is already an active
   member, **When** they submit the invitation, **Then** the system blocks the
   duplicate and explains why.

---

### User Story 2 - Accept or Decline Invitations (Priority: P2)

Invited users can review pending workspace invitations and either accept to join
with the proposed role or decline to avoid unintended membership.

**Why this priority**: Invitation lifecycle completion is needed to turn pending
invites into active members and keep team state accurate.

**Independent Test**: From an invited user's account, open the pending
invitation, accept it, and verify workspace membership is created with the
invited role. Repeat with decline and verify no membership is created.

**Acceptance Scenarios**:

1. **Given** a user has a pending invitation, **When** they accept, **Then**
   they become an active member of that workspace with the invited role.
2. **Given** a user has a pending invitation, **When** they decline, **Then**
   the invitation is marked declined and no membership is created.
3. **Given** an invitation is expired or revoked, **When** the invitee tries to
   accept it, **Then** the system rejects the action with clear guidance.

---

### User Story 3 - Manage Existing Members (Priority: P3)

Workspace owners can manage existing members by updating roles or removing
members, and admins can perform role-permitted member operations to keep access
aligned with team responsibilities.

**Why this priority**: Ongoing access governance is necessary for security and
operations after onboarding is in place.

**Independent Test**: From a workspace owner account, change a member role and
remove a member; verify permissions update immediately and removed users lose
workspace access.

**Acceptance Scenarios**:

1. **Given** an owner views workspace members, **When** they change a member
   from viewer to agent, **Then** the role updates and new permissions apply.
2. **Given** an owner views workspace members, **When** they remove a member,
   **Then** that user no longer has access to workspace resources.
3. **Given** an admin manages members, **When** they attempt an owner-only
   action, **Then** the system denies the action and preserves owner-only
   controls.

---

### Edge Cases

- What happens when an invitation email is sent multiple times for the same
  workspace and recipient?
- How does the system handle invitation acceptance after expiration?
- What happens when the last owner in a workspace would be removed or demoted?
- How does the system handle role updates for users with active ticket
  assignments?
- What happens when an invited email does not yet have an account?

## Constitutional Constraints *(mandatory)*

- **Docker Workflow**: Any setup, verification, and validation steps for this
  feature MUST run through repository Docker workflows.
- **Ownership Boundary**: This feature is a thin vertical slice led by backend
  ownership for membership and invitation rules, with frontend updates limited to
  team management workflows and documentation updates where needed.
- **Tenant/Auth Impact**: Invitation and membership operations MUST be
  workspace-scoped, enforce role permissions, and prevent cross-workspace access
  or data leakage.
- **AI/Async Impact**: No AI processing behavior is introduced; if invitation
  notifications are asynchronous, they MUST preserve reliability and idempotent
  outcomes.
- **Verification Impact**: Backend authorization and tenant-isolation tests are
  required, plus frontend lint/build verification for any UI updates.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST allow workspace owners and admins to create
  workspace-scoped invitations for non-members using an email address and target
  role.
- **FR-002**: System MUST prevent duplicate active invitations for the same
  workspace and recipient.
- **FR-003**: System MUST let invitees view their pending invitations and accept
  or decline each invitation.
- **FR-003a**: System MUST allow invitation acceptance only when the
  authenticated account email exactly matches the invited email.
- **FR-004**: System MUST create workspace membership only when an invitation is
  accepted and valid.
- **FR-005**: System MUST support invitation lifecycle states at minimum:
  pending, accepted, declined, revoked, and expired.
- **FR-005a**: System MUST expire pending invitations 7 days after creation.
- **FR-006**: System MUST allow authorized workspace managers to revoke pending
  invitations before acceptance.
- **FR-007**: System MUST provide a workspace member directory that includes
  member role and membership status.
- **FR-008**: System MUST allow owners to change any member role and allow
  admins to change roles for non-owner members only.
- **FR-009**: System MUST allow owners to remove any non-final owner member and
  allow admins to remove non-owner members only, while preventing invalid actions
  such as removing the last owner.
- **FR-010**: System MUST enforce workspace isolation so invitation and member
  actions never expose or modify another workspace's data.
- **FR-011**: System MUST record invitation and membership change events with
  actor, timestamp, workspace, and action outcome for operational traceability.
- **FR-012**: System MUST present clear user-facing outcomes for invite creation,
  acceptance, decline, revoke, role update, and member removal failures.

### Key Entities *(include if feature involves data)*

- **Workspace Invitation**: Represents a request for a user (identified by
  email) to join a specific workspace with a proposed role and lifecycle status.
- **Workspace Member**: Represents an active relationship between a user and a
  workspace with an assigned role and membership state.
- **Membership Change Event**: Represents a recorded event for invite creation,
  acceptance, decline, revoke, role update, or removal tied to actor and
  timestamp.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: At least 95% of valid invitations are created successfully on first
  attempt during acceptance testing.
- **SC-002**: At least 90% of invited users can accept or decline an invitation
  in under 2 minutes from opening the invitation flow.
- **SC-003**: 100% of unauthorized invitation or member-management attempts are
  blocked in validation testing.
- **SC-004**: 100% of cross-workspace invitation and member access attempts are
  rejected in tenant-isolation testing.
- **SC-005**: At least 95% of role or member changes are reflected to workspace
  managers within 10 seconds in normal operation.

## Assumptions

- Owner and admin roles can invite and revoke invitations, and admins can manage
  non-owner members while owner-only controls remain protected.
- Invitation recipients may already have an account or may create one before
  accepting the invitation.
- Invitation expiration is fixed at 7 days unless amended by future policy.
- Workspace team management is available from role-appropriate owner/admin
  workflows and read-only roles cannot mutate membership.
