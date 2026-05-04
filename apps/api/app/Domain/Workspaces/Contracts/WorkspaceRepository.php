<?php

declare(strict_types=1);

namespace App\Domain\Workspaces\Contracts;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface WorkspaceRepository
{
    /**
     * @param  list<string>|null  $roles
     * @return Collection<int, Workspace>
     */
    public function workspacesForUser(User $user, ?array $roles = null): Collection;

    /**
     * @param  list<string>|null  $roles
     * @return LengthAwarePaginator<int, Workspace>
     */
    public function paginateForUser(User $user, int $perPage, ?array $roles = null): LengthAwarePaginator;

    /**
     * @param  list<string>|null  $roles
     */
    public function findForUserOrFail(User $user, int $workspaceId, ?array $roles = null): Workspace;

    public function createOwnedForUser(User $user, string $name): Workspace;

    /**
     * @param  list<string>  $roles
     */
    public function userHasAnyRole(User $user, array $roles): bool;
}
