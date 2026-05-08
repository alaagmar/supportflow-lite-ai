<?php

declare(strict_types=1);

namespace App\Domain\Workspaces\UseCases;

use App\Models\User;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final class InviteWorkspaceMember
{
    public function handle(User $actor, int $workspaceId, string $email, string $role): WorkspaceInvitation
    {
        $actorRole = WorkspaceMember::query()
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $actor->id)
            ->value('role');

        if (! in_array($actorRole, [WorkspaceMember::ROLE_OWNER, WorkspaceMember::ROLE_ADMIN], true)) {
            abort(403);
        }

        $normalizedEmail = mb_strtolower(trim($email));

        $existingMember = WorkspaceMember::query()
            ->where('workspace_id', $workspaceId)
            ->whereHas('user', fn ($query) => $query->whereRaw('LOWER(email) = ?', [$normalizedEmail]))
            ->exists();

        if ($existingMember) {
            throw ValidationException::withMessages([
                'email' => ['This user is already a workspace member.'],
            ]);
        }

        $pendingExists = WorkspaceInvitation::query()
            ->where('workspace_id', $workspaceId)
            ->where('invited_email', $normalizedEmail)
            ->where('status', WorkspaceInvitation::STATUS_PENDING)
            ->exists();

        if ($pendingExists) {
            throw ValidationException::withMessages([
                'email' => ['A pending invitation already exists for this email.'],
            ]);
        }

        $invitation = WorkspaceInvitation::query()->create([
            'workspace_id' => $workspaceId,
            'invited_email' => $normalizedEmail,
            'invited_role' => $role,
            'status' => WorkspaceInvitation::STATUS_PENDING,
            'invited_by_user_id' => $actor->id,
            'expires_at' => now()->addDays(7),
        ]);

        Log::info('workspace_invitation.created', [
            'workspace_id' => $workspaceId,
            'actor_user_id' => $actor->id,
            'invitation_id' => $invitation->id,
            'invited_email' => $normalizedEmail,
            'invited_role' => $role,
        ]);

        return $invitation;
    }
}
