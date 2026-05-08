<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WorkspaceMember;

class WorkspaceMemberPolicy
{
    public function viewAny(User $user, int $workspaceId): bool
    {
        return WorkspaceMember::query()
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->whereIn('role', [WorkspaceMember::ROLE_OWNER, WorkspaceMember::ROLE_ADMIN])
            ->exists();
    }

    public function update(User $user, WorkspaceMember $target): bool
    {
        $actorRole = $this->roleForWorkspace($user, (int) $target->workspace_id);

        if ($actorRole === WorkspaceMember::ROLE_OWNER) {
            return true;
        }

        return $actorRole === WorkspaceMember::ROLE_ADMIN && $target->role !== WorkspaceMember::ROLE_OWNER;
    }

    public function delete(User $user, WorkspaceMember $target): bool
    {
        return $this->update($user, $target);
    }

    private function roleForWorkspace(User $user, int $workspaceId): ?string
    {
        return WorkspaceMember::query()
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->value('role');
    }
}
