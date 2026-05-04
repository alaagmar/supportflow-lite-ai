<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

class WorkspacePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->workspaceMemberships()->exists();
    }

    public function accessOwnerPortal(User $user): bool
    {
        return $user->workspaceMemberships()
            ->where('role', WorkspaceMember::ROLE_OWNER)
            ->exists();
    }

    public function accessAdminPortal(User $user): bool
    {
        return $user->workspaceMemberships()
            ->whereIn('role', [WorkspaceMember::ROLE_OWNER, WorkspaceMember::ROLE_ADMIN])
            ->exists();
    }

    public function accessStaffPortal(User $user): bool
    {
        return $user->workspaceMemberships()
            ->whereIn('role', [
                WorkspaceMember::ROLE_OWNER,
                WorkspaceMember::ROLE_ADMIN,
                WorkspaceMember::ROLE_AGENT,
                WorkspaceMember::ROLE_VIEWER,
            ])
            ->exists();
    }

    public function create(User $user): bool
    {
        return $user->workspaceMemberships()
            ->where('role', WorkspaceMember::ROLE_OWNER)
            ->exists();
    }

    public function view(User $user, Workspace $workspace): bool
    {
        return $user->workspaceMemberships()
            ->where('workspace_id', $workspace->getKey())
            ->exists();
    }
}
