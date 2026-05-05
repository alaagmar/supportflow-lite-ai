<?php

declare(strict_types=1);

namespace App\Domain\PolicyKnowledgeBase\Support;

use App\Models\PolicyDocument;
use App\Models\User;
use App\Models\WorkspaceMember;

final class ResolvesWorkspacePolicyAccess
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

    public function resolvePolicyDocumentOrFail(int $workspaceId, int $policyId): PolicyDocument
    {
        /** @var PolicyDocument $policyDocument */
        $policyDocument = PolicyDocument::query()
            ->where('workspace_id', $workspaceId)
            ->whereKey($policyId)
            ->firstOrFail();

        return $policyDocument;
    }
}
