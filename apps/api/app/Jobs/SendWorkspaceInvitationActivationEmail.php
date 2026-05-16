<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Workspaces\Support\WorkspaceInvitationActivationTokenService;
use App\Models\WorkspaceInvitation;
use App\Notifications\WorkspaceInvitationActivationNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendWorkspaceInvitationActivationEmail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 30;

    public function __construct(
        public readonly int $invitationId,
        public readonly string $plainToken,
    ) {}

    public function handle(WorkspaceInvitationActivationTokenService $tokens): void
    {
        $invitation = WorkspaceInvitation::query()->find($this->invitationId);

        if (! $invitation || $invitation->status !== WorkspaceInvitation::STATUS_PENDING || $invitation->isExpired()) {
            return;
        }

        $activeToken = $tokens->findActiveByPlainToken($this->plainToken);

        if (! $activeToken || $activeToken->workspace_invitation_id !== $invitation->id) {
            return;
        }

        $frontendUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:3000'), '/');
        $activationUrl = sprintf('%s/staff/activate?token=%s', $frontendUrl, urlencode($this->plainToken));

        Notification::route('mail', $invitation->invited_email)
            ->notify(new WorkspaceInvitationActivationNotification($activationUrl));

        Log::info('invitation_activation.email_sent', [
            'workspace_id' => $invitation->workspace_id,
            'invitation_id' => $invitation->id,
            'invited_email' => $invitation->invited_email,
            'outcome' => 'success',
            'occurred_at' => now()->toIso8601String(),
        ]);
    }
}
