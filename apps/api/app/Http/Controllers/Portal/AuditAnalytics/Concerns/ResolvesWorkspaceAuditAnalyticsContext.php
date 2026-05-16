<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\AuditAnalytics\Concerns;

use App\Domain\AuditAnalytics\Support\ResolvesWorkspaceAuditAccess;
use App\Domain\Identity\Portal;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

trait ResolvesWorkspaceAuditAnalyticsContext
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

    protected function assertWorkspaceAccess(
        User $user,
        int $workspaceId,
        Request $request,
        ResolvesWorkspaceAuditAccess $workspaceAccess,
    ): void {
        $workspaceAccess->assertWorkspaceAccess($user, $workspaceId, $this->portalRoles($request));
    }
}
