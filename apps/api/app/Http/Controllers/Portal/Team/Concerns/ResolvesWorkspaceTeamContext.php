<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Team\Concerns;

use App\Domain\Identity\Portal;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

trait ResolvesWorkspaceTeamContext
{
    protected function authorizePortalAccess(Request $request): void
    {
        Gate::authorize((string) $request->route('portal_ability'), Workspace::class);
    }

    /**
     * @return list<string>
     */
    protected function portalRoles(Request $request): array
    {
        return Portal::rolesFor((string) $request->route('portal'));
    }

    protected function assertWorkspaceAccess(User $user, int $workspaceId, Request $request): void
    {
        $allowedRoles = $this->portalRoles($request);

        $hasAccess = WorkspaceMember::query()
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->whereIn('role', $allowedRoles)
            ->exists();

        abort_unless($hasAccess, 404);
    }
}
