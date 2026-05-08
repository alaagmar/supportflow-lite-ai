<?php

declare(strict_types=1);

namespace App\Domain\Workspaces\UseCases;

use App\Models\User;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class UpdateWorkspaceMemberRole
{
    public function handle(User $actor, int $workspaceId, int $memberId, string $role): WorkspaceMember
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
            throw new ConflictHttpException('Owner roles cannot be changed through this endpoint.');
        }

        $previousRole = $target->role;

        $target->forceFill(['role' => $role])->save();

        Log::info('workspace_member.role_updated', [
            'workspace_id' => $workspaceId,
            'actor_user_id' => $actor->id,
            'target_user_id' => (int) $target->user_id,
            'previous_role' => $previousRole,
            'new_role' => $role,
        ]);

        return $target->refresh();
    }
}
