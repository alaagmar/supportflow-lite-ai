<?php

declare(strict_types=1);

namespace App\Domain\Identity\UseCases;

use App\Domain\Identity\Contracts\UserRepository;
use App\Domain\Identity\Data\AuthSessionData;
use App\Domain\Identity\Portal;
use App\Domain\Workspaces\Contracts\WorkspaceRepository;
use App\Exceptions\PortalAccessDeniedException;
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

        if (! $this->workspaces->userHasAnyRole($user, $roles)) {
            throw new PortalAccessDeniedException;
        }

        return new AuthSessionData(
            token: $user->createToken('supportflow-web', ['*'], now()->addHours(8))->plainTextToken,
            portal: $portal,
            user: $user,
            workspaces: $this->workspaces->workspacesForUser($user, $roles),
        );
    }
}
