<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;

class WorkspaceInvitationPolicy
{
    public function viewAny(User $user, int $workspaceId): bool
    {
        return $this->isOwnerOrAdmin($user, $workspaceId);
    }

    public function create(User $user, int $workspaceId): bool
    {
        return $this->isOwnerOrAdmin($user, $workspaceId);
    }

    public function revoke(User $user, WorkspaceInvitation $invitation): bool
    {
        return $this->isOwnerOrAdmin($user, (int) $invitation->workspace_id);
    }

    public function respond(User $user, WorkspaceInvitation $invitation): bool
    {
        return strcasecmp($user->email, (string) $invitation->invited_email) === 0;
    }

    private function isOwnerOrAdmin(User $user, int $workspaceId): bool
    {
        return WorkspaceMember::query()
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->whereIn('role', [WorkspaceMember::ROLE_OWNER, WorkspaceMember::ROLE_ADMIN])
            ->exists();
    }
}
