<?php

declare(strict_types=1);

namespace App\Domain\Workspaces\UseCases;

use App\Domain\Workspaces\Contracts\WorkspaceRepository;
use App\Models\User;
use App\Models\Workspace;

final readonly class ShowWorkspace
{
    public function __construct(private WorkspaceRepository $workspaces) {}

    /**
     * @param  list<string>  $roles
     */
    public function handle(User $user, int $workspaceId, array $roles): Workspace
    {
        return $this->workspaces->findForUserOrFail($user, $workspaceId, $roles);
    }
}
