<?php

declare(strict_types=1);

namespace App\Domain\Workspaces\Support;

use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceInvitationActivationToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

final class WorkspaceInvitationActivationTokenService
{
    /**
     * @return array{token: WorkspaceInvitationActivationToken, plain_token: string}
     */
    public function issueInitialToken(WorkspaceInvitation $invitation): array
    {
        return $this->issueToken($invitation, isResend: false);
    }

    /**
     * @return array{token: WorkspaceInvitationActivationToken, plain_token: string}
     */
    public function issueReplacementToken(WorkspaceInvitation $invitation): array
    {
        return $this->issueToken($invitation, isResend: true);
    }

    public function findActiveByPlainToken(string $plainToken): ?WorkspaceInvitationActivationToken
    {
        $hash = hash('sha256', $plainToken);

        return WorkspaceInvitationActivationToken::query()
            ->where('token_hash', $hash)
            ->whereNull('used_at')
            ->whereNull('invalidated_at')
            ->where('expires_at', '>', now())
            ->with('workspaceInvitation')
            ->first();
    }

    public function markUsed(WorkspaceInvitationActivationToken $token): void
    {
        $token->forceFill([
            'used_at' => now(),
        ])->save();
    }

    /**
     * @return array{0: Carbon, 1: int}
     */
    private function nextResendWindowState(WorkspaceInvitation $invitation): array
    {
        $latest = WorkspaceInvitationActivationToken::query()
            ->where('workspace_invitation_id', $invitation->id)
            ->latest('id')
            ->first();

        if (! $latest || $latest->resend_window_started_at === null || $latest->resend_window_started_at->lt(now()->subDay())) {
            return [now(), 1];
        }

        return [$latest->resend_window_started_at, $latest->resend_count_window + 1];
    }

    /**
     * @return array{token: WorkspaceInvitationActivationToken, plain_token: string}
     */
    private function issueToken(WorkspaceInvitation $invitation, bool $isResend): array
    {
        [$windowStart, $windowCount] = $isResend
            ? $this->nextResendWindowState($invitation)
            : [null, 0];

        if ($isResend && $windowCount > 3) {
            throw new TooManyRequestsHttpException(null, 'Activation resend limit reached. Try again later.');
        }

        WorkspaceInvitationActivationToken::query()
            ->where('workspace_invitation_id', $invitation->id)
            ->whereNull('used_at')
            ->whereNull('invalidated_at')
            ->update([
                'invalidated_at' => now(),
                'updated_at' => now(),
            ]);

        $plainToken = Str::random(96);

        $token = WorkspaceInvitationActivationToken::query()->create([
            'workspace_invitation_id' => $invitation->id,
            'invited_email' => $invitation->invited_email,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->addDays(7),
            'issued_at' => now(),
            'resend_count_window' => $windowCount,
            'resend_window_started_at' => $windowStart,
            'last_sent_at' => now(),
        ]);

        return [
            'token' => $token,
            'plain_token' => $plainToken,
        ];
    }
}
