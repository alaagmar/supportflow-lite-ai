<?php

declare(strict_types=1);

namespace App\Domain\AuditAnalytics\Support;

use App\Models\Ticket;
use App\Models\User;
use App\Models\WorkspaceMember;

final class ResolvesWorkspaceAuditAccess
{
    /**
     * @param  list<string>  $roles
     */
    public function assertWorkspaceAccess(User $user, int $workspaceId, array $roles): void
    {
        WorkspaceMember::query()
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $user->getKey())
            ->whereIn('role', $roles)
            ->firstOrFail();
    }

    public function resolveWorkspaceTicketOrFail(int $workspaceId, int $ticketId): Ticket
    {
        /** @var Ticket $ticket */
        $ticket = Ticket::query()
            ->where('workspace_id', $workspaceId)
            ->whereKey($ticketId)
            ->firstOrFail();

        return $ticket;
    }
}
