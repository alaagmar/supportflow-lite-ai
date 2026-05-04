<?php

declare(strict_types=1);

namespace App\Domain\Identity\UseCases;

use App\Domain\Identity\Data\CurrentSessionData;
use App\Domain\Identity\Portal;
use App\Domain\Workspaces\Contracts\WorkspaceRepository;
use App\Exceptions\PortalAccessDeniedException;
use App\Models\User;

final readonly class CurrentSession
{
    public function __construct(private WorkspaceRepository $workspaces) {}

    /**
     * @throws PortalAccessDeniedException
     */
    public function handle(User $user, string $portal): CurrentSessionData
    {
        $roles = Portal::rolesFor($portal);

        if (! $this->workspaces->userHasAnyRole($user, $roles)) {
            throw new PortalAccessDeniedException;
        }

        return new CurrentSessionData(
            user: $user,
            workspaces: $this->workspaces->workspacesForUser($user, $roles),
        );
    }
}
