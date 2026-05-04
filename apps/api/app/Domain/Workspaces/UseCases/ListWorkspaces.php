<?php

declare(strict_types=1);

namespace App\Domain\Workspaces\UseCases;

use App\Domain\Workspaces\Contracts\WorkspaceRepository;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class ListWorkspaces
{
    public function __construct(private WorkspaceRepository $workspaces) {}

    /**
     * @param  list<string>  $roles
     * @return LengthAwarePaginator<int, Workspace>
     */
    public function handle(User $user, int $perPage, array $roles): LengthAwarePaginator
    {
        return $this->workspaces->paginateForUser($user, $perPage, $roles);
    }
}
