<?php

declare(strict_types=1);

namespace App\Domain\Workspaces\UseCases;

use App\Models\User;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class AcceptWorkspaceInvitation
{
    public function handle(User $user, int $workspaceId, int $invitationId): WorkspaceInvitation
    {
        $invitation = WorkspaceInvitation::query()
            ->where('workspace_id', $workspaceId)
            ->findOrFail($invitationId);

        if (strcasecmp($user->email, (string) $invitation->invited_email) !== 0) {
            throw new ConflictHttpException('Only the invited email account can accept this invitation.');
        }

        if ($invitation->status !== WorkspaceInvitation::STATUS_PENDING) {
            throw new ConflictHttpException('This invitation can no longer be accepted.');
        }

        if ($invitation->isExpired()) {
            $invitation->forceFill(['status' => WorkspaceInvitation::STATUS_EXPIRED])->save();

            throw new ConflictHttpException('This invitation has expired.');
        }

        $membershipExists = WorkspaceMember::query()
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->exists();

        if ($membershipExists) {
            throw new ConflictHttpException('You are already a member of this workspace.');
        }

        DB::transaction(function () use ($user, $workspaceId, $invitation): void {
            WorkspaceMember::query()->create([
                'workspace_id' => $workspaceId,
                'user_id' => $user->id,
                'role' => $invitation->invited_role,
            ]);

            $invitation->forceFill([
                'status' => WorkspaceInvitation::STATUS_ACCEPTED,
                'accepted_by_user_id' => $user->id,
                'accepted_at' => now(),
            ])->save();
        });

        Log::info('workspace_invitation.accepted', [
            'workspace_id' => $workspaceId,
            'actor_user_id' => $user->id,
            'invitation_id' => $invitation->id,
            'assigned_role' => $invitation->invited_role,
        ]);

        return $invitation->refresh();
    }
}
