<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WorkspaceMember;

class AuditAnalyticsPolicy
{
    public function viewWorkspaceAudit(User $user, int $workspaceId): bool
    {
        return $this->hasReadAuditRole($user, $workspaceId);
    }

    public function viewTicketAudit(User $user, int $workspaceId): bool
    {
        return $this->hasReadAuditRole($user, $workspaceId);
    }

    public function viewWorkspaceAnalytics(User $user, int $workspaceId): bool
    {
        return $this->hasReadAuditRole($user, $workspaceId);
    }

    private function hasReadAuditRole(User $user, int $workspaceId): bool
    {
        return WorkspaceMember::query()
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->whereIn('role', [
                WorkspaceMember::ROLE_OWNER,
                WorkspaceMember::ROLE_ADMIN,
                WorkspaceMember::ROLE_VIEWER,
            ])
            ->exists();
    }
}
