# Feature Specification: Invited User Account Activation

**Feature Branch**: `[002-invited-user-activation]`  
**Created**: 2026-05-08  
**Status**: Draft  
**Input**: User description: "after inviting or accepting an admin/agent/viewer send an email to the mail associated with link. when opening this link he should set a password and become authorized to login to his account using his criendtials"

## Clarifications

### Session 2026-05-08

- Q: Should this flow target inactive/deactivated users or team invitations? -> A: Target users from team-invite-management `WorkspaceInvitation`; this is not an inactive/deactivated user flow.
- Q: If invited email already belongs to an existing active user account, what should happen? -> A: Do not send activation email; user signs in normally and accepts the invitation from pending invites.
- Q: When should activation email be sent for new invitees requiring credentials? -> A: Send it immediately when the invitation is created.
- Q: After password setup via activation, should membership be auto-created? -> A: No; activation only enables account sign-in, and membership is created only when the invitation is explicitly accepted.
- Q: What should be the activation-link expiration window? -> A: Activation links expire after 7 days.
- Q: What resend limit should apply to replacement activation emails? -> A: Maximum 3 resends per 24 hours per invitation.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Activate invited account via email link (Priority: P1)

As an invited workspace member (admin, agent, or viewer), I receive an activation email with a secure link, set my password from that link, and then sign in with my own credentials.

**Why this priority**: This is the core value of the feature. Without account activation and password setup, invited users cannot access the workspace.

**Independent Test**: Send an invitation to a new email, open the email link, set a password, and verify that the user can sign in successfully with email and password.

**Acceptance Scenarios**:

1. **Given** a new admin/agent/viewer is invited, **When** the invitation is created, **Then** an activation email is sent to the invited email with a unique activation link.
2. **Given** the invited user opens a valid activation link, **When** they submit a valid password, **Then** their account becomes active for sign-in and they can log in with that email and password.

---

### User Story 2 - Handle expired or invalid activation links (Priority: P2)

As an invited workspace member, if my activation link is expired or invalid, I can request a new link and still complete account activation.

**Why this priority**: Link expiry and copy/paste mistakes are common. Recovery flow prevents onboarding drop-off and support burden.

**Independent Test**: Attempt activation with an expired or malformed link and verify the user is blocked from password setup, sees clear guidance, and can request a replacement link.

**Acceptance Scenarios**:

1. **Given** an expired activation link, **When** the user opens it, **Then** the system denies activation and shows a clear message with next steps.
2. **Given** an invited user requests a new activation email, **When** a new link is issued, **Then** only the newest valid link can complete activation.

---

### User Story 3 - Prevent unauthorized pre-activation login (Priority: P3)

As a workspace owner/admin, I need confidence that invited users cannot sign in until they complete password setup from the activation flow.

**Why this priority**: This protects access control and ensures every invited user verifies email ownership before account use.

**Independent Test**: Invite a user and attempt to sign in before activation; verify sign-in is denied. Complete activation and verify sign-in succeeds.

**Acceptance Scenarios**:

1. **Given** an invited user has not completed activation, **When** they try to sign in, **Then** access is denied with a clear message that activation is required.

---

### Edge Cases

- Invitations targeting an email with an existing active account skip activation email and require normal sign-in plus invitation acceptance.
- Replacement activation emails are limited to 3 resends per 24-hour window per invitation.
- What happens when a user opens an activation link after the account has already been activated?
- How does the system behave if email delivery fails temporarily?

## Constitutional Constraints *(mandatory)*

- **Docker Workflow**: Any verification for this feature must run through the project Docker workflow and existing Make/compose commands.
- **Ownership Boundary**: The feature spans backend identity/invitation behavior and frontend activation/sign-in experience as one thin vertical slice.
- **Tenant/Auth Impact**: Applies to workspace member roles admin, agent, and viewer; activation must not grant access beyond invited role and workspace membership.
- **AI/Async Impact**: No AI processing is required; asynchronous behavior is limited to invitation/activation email delivery.
- **Verification Impact**: Validate invitation, activation, and login flows with role-aware acceptance checks, including failure and recovery paths.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST send an account activation email to the invited email address immediately when an admin, agent, or viewer invitation is created and the invitee still requires credential setup.
- **FR-001a**: System MUST apply this activation flow to users represented by a pending workspace invitation, not to already deactivated user accounts.
- **FR-001b**: System MUST NOT send activation email for an invited email that already belongs to an active account; that user signs in with existing credentials and completes normal invitation acceptance.
- **FR-002**: System MUST include a unique, single-use activation link in each activation email.
- **FR-002a**: System MUST expire activation links 7 days after issuance.
- **FR-003**: System MUST require invited users to set a password through the activation link before their first successful sign-in.
- **FR-004**: System MUST deny sign-in attempts for invited users who have not completed activation.
- **FR-005**: System MUST mark the activation link as invalid immediately after successful password setup.
- **FR-006**: System MUST enforce configured password quality rules during activation and provide clear validation feedback when requirements are not met.
- **FR-007**: System MUST provide a recovery path for expired or invalid activation links so invited users can receive a replacement activation email.
- **FR-007a**: System MUST limit replacement activation emails to a maximum of 3 resends per 24 hours per invitation.
- **FR-008**: System MUST retain invited role and workspace assignment after activation, without granting elevated permissions.
- **FR-008a**: System MUST NOT auto-create workspace membership during activation; membership is created only through the invitation acceptance action.
- **FR-009**: System MUST record activation lifecycle events (email sent, activation completed, activation failed/expired) for operational traceability.

### Key Entities *(include if feature involves data)*

- **Workspace Invitation Recipient**: A person represented by a workspace invitation for role (admin, agent, viewer), identified by email and linked to a specific workspace membership intent.
- **Activation Token**: A secure, unique, time-limited token that ties an invitation recipient to one password-setup action.
- **Account Credential State**: The invited user account status indicating whether password setup is pending or completed and therefore whether sign-in is allowed.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: At least 95% of invitation-triggered activation emails are received by recipients within 2 minutes under normal operating conditions.
- **SC-002**: At least 90% of invited users who open the activation link complete password setup and first sign-in in under 5 minutes.
- **SC-003**: 100% of pre-activation sign-in attempts for invited users are blocked until password setup is completed.
- **SC-004**: At least 95% of expired/invalid link cases are successfully recovered by users through replacement-link flow without manual admin intervention.

## Assumptions

- Activation applies to users in the workspace invitation lifecycle and does not represent account reactivation for deactivated users.
- Activation links expire after 7 days and can be replaced through a resend/recovery flow.
- Email ownership verification is satisfied by successful use of the activation link sent to the invited address.
- Role assignment (admin, agent, viewer) is determined at invite time and is not changed by the activation process.
