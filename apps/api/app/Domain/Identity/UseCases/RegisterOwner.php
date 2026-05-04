<?php

declare(strict_types=1);

namespace App\Domain\Identity\UseCases;

use App\Domain\Identity\Contracts\UserRepository;
use App\Domain\Identity\Data\AuthSessionData;
use App\Domain\Identity\Portal;
use App\Domain\Workspaces\Contracts\WorkspaceRepository;
use Illuminate\Support\Facades\DB;

final readonly class RegisterOwner
{
    public function __construct(
        private UserRepository $users,
        private WorkspaceRepository $workspaces,
    ) {}

    public function handle(string $name, string $email, string $password, string $workspaceName): AuthSessionData
    {
        return DB::transaction(function () use ($name, $email, $password, $workspaceName): AuthSessionData {
            $user = $this->users->create($name, $email, $password);
            $this->workspaces->createOwnedForUser($user, $workspaceName);

            return new AuthSessionData(
                token: $user->createToken('supportflow-web', ['*'], now()->addHours(8))->plainTextToken,
                portal: Portal::OWNER,
                user: $user,
                workspaces: $this->workspaces->workspacesForUser($user, Portal::rolesFor(Portal::OWNER)),
            );
        });
    }
}
