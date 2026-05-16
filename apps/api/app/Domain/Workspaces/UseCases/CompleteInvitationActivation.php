<?php

declare(strict_types=1);

namespace App\Domain\Workspaces\UseCases;

use App\Domain\Workspaces\Support\WorkspaceInvitationActivationTokenService;
use App\Models\User;
use App\Models\WorkspaceInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final readonly class CompleteInvitationActivation
{
    public function __construct(
        private WorkspaceInvitationActivationTokenService $activationTokens,
    ) {}

    public function handle(string $token, string $password): User
    {
        $activationToken = $this->activationTokens->findActiveByPlainToken($token);

        if (! $activationToken) {
            throw new ConflictHttpException('The activation link is invalid or expired.');
        }

        $invitation = $activationToken->workspaceInvitation;

        if ($invitation->status !== WorkspaceInvitation::STATUS_PENDING || $invitation->isExpired()) {
            throw new ConflictHttpException('The invitation is no longer pending.');
        }

        /** @var User $user */
        $user = DB::transaction(function () use ($invitation, $password, $activationToken): User {
            $email = mb_strtolower((string) $invitation->invited_email);

            $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

            if (! $user) {
                $user = User::query()->create([
                    'name' => $this->nameFromEmail($email),
                    'email' => $email,
                    'password' => $password,
                ]);

                $user->forceFill([
                    'email_verified_at' => now(),
                ])->save();
            } else {
                $user->forceFill([
                    'password' => $password,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();
            }

            $this->activationTokens->markUsed($activationToken);

            // Auto-accept: setting a password via an invitation link IS acceptance.
            // Create the workspace membership immediately so the user can log into
            // their proper portal without a separate acceptance step.
            $alreadyMember = $user->workspaceMemberships()
                ->where('workspace_id', $invitation->workspace_id)
                ->exists();

            if (! $alreadyMember) {
                $user->workspaceMemberships()->create([
                    'workspace_id' => $invitation->workspace_id,
                    'role' => $invitation->invited_role,
                ]);
            }

            $invitation->forceFill([
                'status' => WorkspaceInvitation::STATUS_ACCEPTED,
                'accepted_by_user_id' => $user->id,
                'accepted_at' => now(),
            ])->save();

            return $user;
        });

        Log::info('invitation_activation.completed', [
            'workspace_id' => $invitation->workspace_id,
            'invitation_id' => $invitation->id,
            'invited_email' => $invitation->invited_email,
            'actor_type' => 'user',
            'assigned_role' => $invitation->invited_role,
            'outcome' => 'success',
            'occurred_at' => now()->toIso8601String(),
            'user_id' => $user->id,
        ]);

        return $user;
    }

    private function nameFromEmail(string $email): string
    {
        $localPart = (string) str($email)->before('@');
        $name = str_replace(['.', '_', '-'], ' ', $localPart);
        $name = trim(ucwords($name));

        return $name !== '' ? $name : 'SupportFlow User';
    }
}
