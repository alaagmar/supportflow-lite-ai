<?php

declare(strict_types=1);

namespace App\Domain\Workspaces\UseCases;

use App\Domain\Workspaces\Support\WorkspaceInvitationActivationTokenService;
use App\Jobs\SendWorkspaceInvitationActivationEmail;
use App\Models\User;
use App\Models\WorkspaceInvitation;
use Illuminate\Support\Facades\Log;

final readonly class ResendInvitationActivation
{
    public function __construct(
        private WorkspaceInvitationActivationTokenService $activationTokens,
    ) {}

    public function handle(string $email, int $workspaceId): bool
    {
        $normalizedEmail = mb_strtolower(trim($email));

        $invitation = WorkspaceInvitation::query()
            ->where('workspace_id', $workspaceId)
            ->whereRaw('LOWER(invited_email) = ?', [$normalizedEmail])
            ->where('status', WorkspaceInvitation::STATUS_PENDING)
            ->latest('id')
            ->first();

        if (! $invitation || $invitation->isExpired()) {
            return false;
        }

        $existingUser = User::query()
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->exists();

        if ($existingUser) {
            return false;
        }

        $issuedToken = $this->activationTokens->issueReplacementToken($invitation);

        SendWorkspaceInvitationActivationEmail::dispatch(
            invitationId: $invitation->id,
            plainToken: $issuedToken['plain_token'],
        );

        Log::info('invitation_activation.email_resent', [
            'workspace_id' => $invitation->workspace_id,
            'invitation_id' => $invitation->id,
            'invited_email' => $invitation->invited_email,
            'actor_type' => 'system',
            'outcome' => 'success',
            'occurred_at' => now()->toIso8601String(),
        ]);

        return true;
    }
}
