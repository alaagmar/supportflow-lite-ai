<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Tickets;

use App\Domain\Identity\Portal;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

trait ResolvesPortalContext
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
}
