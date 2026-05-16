<?php

declare(strict_types=1);

namespace App\Domain\Identity\Support;

use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceInvitationActivationToken;
use Illuminate\Validation\ValidationException;

final class PendingInvitationActivationGuard
{
    /**
     * @throws ValidationException
     */
    public function assertNoPendingActivation(string $email): void
    {
        $normalizedEmail = mb_strtolower(trim($email));

        $hasPendingActivation = WorkspaceInvitationActivationToken::query()
            ->whereRaw('LOWER(invited_email) = ?', [$normalizedEmail])
            ->whereNull('used_at')
            ->whereNull('invalidated_at')
            ->where('expires_at', '>', now())
            ->whereHas('workspaceInvitation', function ($query): void {
                $query
                    ->where('status', WorkspaceInvitation::STATUS_PENDING)
                    ->where('expires_at', '>', now());
            })
            ->exists();

        if ($hasPendingActivation) {
            throw ValidationException::withMessages([
                'email' => ['Activation required. Complete your invite activation email before signing in.'],
            ]);
        }
    }
}
