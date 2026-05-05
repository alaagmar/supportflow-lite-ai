<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PolicyDocument;
use App\Models\User;
use App\Models\WorkspaceMember;

class PolicyDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->workspaceMemberships()->exists();
    }

    public function view(User $user, PolicyDocument $policyDocument): bool
    {
        return $user->workspaceMemberships()
            ->where('workspace_id', $policyDocument->workspace_id)
            ->exists();
    }

    public function create(User $user, int $workspaceId): bool
    {
        return $this->hasManagementRole($user, $workspaceId);
    }

    public function update(User $user, PolicyDocument $policyDocument): bool
    {
        return $this->hasManagementRole($user, $policyDocument->workspace_id);
    }

    public function archive(User $user, PolicyDocument $policyDocument): bool
    {
        return $this->hasManagementRole($user, $policyDocument->workspace_id);
    }

    public function unarchive(User $user, PolicyDocument $policyDocument): bool
    {
        return $this->hasManagementRole($user, $policyDocument->workspace_id);
    }

    public function retrieve(User $user, int $workspaceId): bool
    {
        return $user->workspaceMemberships()
            ->where('workspace_id', $workspaceId)
            ->exists();
    }

    private function hasManagementRole(User $user, int $workspaceId): bool
    {
        return $user->workspaceMemberships()
            ->where('workspace_id', $workspaceId)
            ->whereIn('role', [
                WorkspaceMember::ROLE_OWNER,
                WorkspaceMember::ROLE_ADMIN,
            ])
            ->exists();
    }
}
