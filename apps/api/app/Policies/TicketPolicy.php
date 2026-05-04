<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use App\Models\WorkspaceMember;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->workspaceMemberships()->exists();
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $user->workspaceMemberships()
            ->where('workspace_id', $ticket->workspace_id)
            ->exists();
    }

    public function create(User $user, int $workspaceId): bool
    {
        return $user->workspaceMemberships()
            ->where('workspace_id', $workspaceId)
            ->whereIn('role', [
                WorkspaceMember::ROLE_OWNER,
                WorkspaceMember::ROLE_ADMIN,
                WorkspaceMember::ROLE_AGENT,
            ])
            ->exists();
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->workspaceMemberships()
            ->where('workspace_id', $ticket->workspace_id)
            ->whereIn('role', [
                WorkspaceMember::ROLE_OWNER,
                WorkspaceMember::ROLE_ADMIN,
                WorkspaceMember::ROLE_AGENT,
            ])
            ->exists();
    }

    public function updateStatus(User $user, Ticket $ticket): bool
    {
        return $this->update($user, $ticket);
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->workspaceMemberships()
            ->where('workspace_id', $ticket->workspace_id)
            ->whereIn('role', [
                WorkspaceMember::ROLE_OWNER,
                WorkspaceMember::ROLE_ADMIN,
            ])
            ->exists();
    }
}
