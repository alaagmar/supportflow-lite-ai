<?php

declare(strict_types=1);

namespace App\Domain\Workspaces\UseCases;

use App\Models\User;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class RevokeWorkspaceInvitation
{
    public function handle(User $actor, int $workspaceId, int $invitationId): WorkspaceInvitation
    {
        $invitation = WorkspaceInvitation::query()
            ->where('workspace_id', $workspaceId)
            ->findOrFail($invitationId);

        $actorRole = WorkspaceMember::query()
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $actor->id)
            ->value('role');

        if (! in_array($actorRole, [WorkspaceMember::ROLE_OWNER, WorkspaceMember::ROLE_ADMIN], true)) {
            abort(403);
        }

        if ($invitation->status !== WorkspaceInvitation::STATUS_PENDING) {
            throw new ConflictHttpException('Only pending invitations can be revoked.');
        }

        $invitation->forceFill([
            'status' => WorkspaceInvitation::STATUS_REVOKED,
            'revoked_at' => now(),
        ])->save();

        Log::info('workspace_invitation.revoked', [
            'workspace_id' => $workspaceId,
            'actor_user_id' => $actor->id,
            'invitation_id' => $invitation->id,
        ]);

        return $invitation->refresh();
    }
}
