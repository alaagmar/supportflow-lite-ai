<?php

declare(strict_types=1);

namespace App\Domain\Identity\UseCases;

use App\Domain\Identity\Contracts\UserRepository;
use App\Domain\Identity\Data\AuthSessionData;
use App\Domain\Identity\Portal;
use App\Domain\Workspaces\Contracts\WorkspaceRepository;
use App\Exceptions\PortalAccessDeniedException;
use App\Models\WorkspaceInvitation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final readonly class AuthenticatePortalUser
{
    public function __construct(
        private UserRepository $users,
        private WorkspaceRepository $workspaces,
    ) {}

    /**
     * @throws PortalAccessDeniedException
     * @throws ValidationException
     */
    public function handle(string $email, string $password, string $portal): AuthSessionData
    {
        $user = $this->users->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $roles = Portal::rolesFor($portal);

        $hasRoleForPortal = $this->workspaces->userHasAnyRole($user, $roles);

        if (! $hasRoleForPortal && ! $this->canAccessStaffPortalWithPendingInvitation($portal, $user->email)) {
            throw new PortalAccessDeniedException;
        }

        return new AuthSessionData(
            token: $user->createToken('supportflow-web', ['*'], now()->addHours(8))->plainTextToken,
            portal: $portal,
            user: $user,
            workspaces: $this->workspaces->workspacesForUser($user, $roles),
        );
    }

    /**
     * Allow a user with a pending invitation to log into the staff portal before
     * they have accepted and received a workspace membership row.
     * This only applies to existing users who receive no activation email and
     * must accept their invitation manually via the staff invitations page.
     */
    private function canAccessStaffPortalWithPendingInvitation(string $portal, string $email): bool
    {
        if ($portal !== Portal::STAFF) {
            return false;
        }

        return WorkspaceInvitation::query()
            ->whereRaw('LOWER(invited_email) = ?', [mb_strtolower($email)])
            ->where('status', WorkspaceInvitation::STATUS_PENDING)
            ->where('expires_at', '>', now())
            ->exists();
    }
}
