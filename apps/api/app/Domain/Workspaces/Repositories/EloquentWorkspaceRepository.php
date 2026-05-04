<?php

declare(strict_types=1);

namespace App\Domain\Workspaces\Repositories;

use App\Domain\Workspaces\Contracts\WorkspaceRepository;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class EloquentWorkspaceRepository implements WorkspaceRepository
{
    public function workspacesForUser(User $user, ?array $roles = null): Collection
    {
        return $this->workspacesQuery($user, $roles)
            ->orderBy('workspaces.name')
            ->get();
    }

    public function paginateForUser(User $user, int $perPage, ?array $roles = null): LengthAwarePaginator
    {
        return $this->workspacesQuery($user, $roles)
            ->orderBy('workspaces.name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findForUserOrFail(User $user, int $workspaceId, ?array $roles = null): Workspace
    {
        /** @var Workspace $workspace */
        $workspace = $this->workspacesQuery($user, $roles)
            ->where('workspaces.id', $workspaceId)
            ->firstOrFail();

        return $workspace;
    }

    public function createOwnedForUser(User $user, string $name): Workspace
    {
        /** @var Workspace $workspace */
        $workspace = Workspace::query()->create([
            'name' => $name,
            'slug' => Workspace::uniqueSlugForName($name),
        ]);

        WorkspaceMember::query()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'role' => WorkspaceMember::ROLE_OWNER,
        ]);

        return $this->findForUserOrFail($user, (int) $workspace->id, [WorkspaceMember::ROLE_OWNER]);
    }

    public function userHasAnyRole(User $user, array $roles): bool
    {
        return $user->workspaceMemberships()
            ->whereIn('role', $roles)
            ->exists();
    }

    /**
     * @param  list<string>|null  $roles
     * @return BelongsToMany<Workspace, $user>
     */
    private function workspacesQuery(User $user, ?array $roles): BelongsToMany
    {
        $query = $user->workspaces();

        if ($roles !== null) {
            $query->wherePivotIn('role', $roles);
        }

        return $query;
    }
}
