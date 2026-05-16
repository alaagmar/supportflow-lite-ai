<?php

declare(strict_types=1);

namespace App\Domain\AuditAnalytics\Support;

final class AuditEventAction
{
    public const TICKET_CREATED = 'ticket.created';

    public const TICKET_PROCESSING_STARTED = 'ticket.processing_started';

    public const TICKET_DRAFT_APPROVED = 'ticket.draft_approved';

    public const TICKET_DRAFT_REJECTED = 'ticket.draft_rejected';

    public const TICKET_RESOLVED = 'ticket.resolved';

    public const POLICY_CREATED = 'policy.created';

    public const POLICY_UPDATED = 'policy.updated';

    public const POLICY_ARCHIVED = 'policy.archived';

    public const POLICY_UNARCHIVED = 'policy.unarchived';

    public const INVITATION_CREATED = 'invitation.created';

    public const INVITATION_REVOKED = 'invitation.revoked';

    public const INVITATION_ACCEPTED = 'invitation.accepted';

    public const INVITATION_DECLINED = 'invitation.declined';

    public const MEMBER_ROLE_UPDATED = 'member.role_updated';

    public const MEMBER_REMOVED = 'member.removed';

    public const AI_CLASSIFICATION_COMPLETED = 'ai.classification.completed';

    public const AI_DRAFT_COMPLETED = 'ai.draft.completed';

    /**
     * @var list<string>
     */
    public const VALUES = [
        self::TICKET_CREATED,
        self::TICKET_PROCESSING_STARTED,
        self::TICKET_DRAFT_APPROVED,
        self::TICKET_DRAFT_REJECTED,
        self::TICKET_RESOLVED,
        self::POLICY_CREATED,
        self::POLICY_UPDATED,
        self::POLICY_ARCHIVED,
        self::POLICY_UNARCHIVED,
        self::INVITATION_CREATED,
        self::INVITATION_REVOKED,
        self::INVITATION_ACCEPTED,
        self::INVITATION_DECLINED,
        self::MEMBER_ROLE_UPDATED,
        self::MEMBER_REMOVED,
        self::AI_CLASSIFICATION_COMPLETED,
        self::AI_DRAFT_COMPLETED,
    ];
}
