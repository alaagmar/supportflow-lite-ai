<?php

declare(strict_types=1);

namespace App\Domain\Workspaces\UseCases;

use App\Models\User;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class RemoveWorkspaceMember
{
    public function handle(User $actor, int $workspaceId, int $memberId): void
    {
        $target = WorkspaceMember::query()
            ->where('workspace_id', $workspaceId)
            ->findOrFail($memberId);

        $actorRole = WorkspaceMember::query()
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $actor->id)
            ->value('role');

        if ($actorRole === WorkspaceMember::ROLE_ADMIN && $target->role === WorkspaceMember::ROLE_OWNER) {
            abort(403);
        }

        if ($target->role === WorkspaceMember::ROLE_OWNER) {
            $ownerCount = WorkspaceMember::query()
                ->where('workspace_id', $workspaceId)
                ->where('role', WorkspaceMember::ROLE_OWNER)
                ->count();

            if ($ownerCount <= 1) {
                throw new ConflictHttpException('The last workspace owner cannot be removed.');
            }
        }

        $targetRole = $target->role;
        $targetUserId = (int) $target->user_id;

        $target->delete();

        Log::info('workspace_member.removed', [
            'workspace_id' => $workspaceId,
            'actor_user_id' => $actor->id,
            'target_user_id' => $targetUserId,
            'target_role' => $targetRole,
        ]);
    }
}
