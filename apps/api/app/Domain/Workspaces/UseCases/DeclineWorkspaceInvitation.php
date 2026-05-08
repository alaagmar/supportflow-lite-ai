<?php

declare(strict_types=1);

namespace App\Domain\Workspaces\UseCases;

use App\Models\User;
use App\Models\WorkspaceInvitation;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class DeclineWorkspaceInvitation
{
    public function handle(User $user, int $workspaceId, int $invitationId): WorkspaceInvitation
    {
        $invitation = WorkspaceInvitation::query()
            ->where('workspace_id', $workspaceId)
            ->findOrFail($invitationId);

        if (strcasecmp($user->email, (string) $invitation->invited_email) !== 0) {
            throw new ConflictHttpException('Only the invited email account can decline this invitation.');
        }

        if ($invitation->status !== WorkspaceInvitation::STATUS_PENDING) {
            throw new ConflictHttpException('This invitation can no longer be declined.');
        }

        if ($invitation->isExpired()) {
            $invitation->forceFill(['status' => WorkspaceInvitation::STATUS_EXPIRED])->save();

            throw new ConflictHttpException('This invitation has expired.');
        }

        $invitation->forceFill([
            'status' => WorkspaceInvitation::STATUS_DECLINED,
            'declined_at' => now(),
        ])->save();

        Log::info('workspace_invitation.declined', [
            'workspace_id' => $workspaceId,
            'actor_user_id' => $user->id,
            'invitation_id' => $invitation->id,
        ]);

        return $invitation->refresh();
    }
}
