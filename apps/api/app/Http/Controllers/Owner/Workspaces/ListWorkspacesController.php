<?php

declare(strict_types=1);

namespace App\Http\Controllers\Owner\Workspaces;

use App\Domain\Identity\Portal;
use App\Domain\Workspaces\UseCases\ListWorkspaces;
use App\Http\Controllers\Controller;
use App\Http\Resources\Workspaces\WorkspaceResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ListWorkspacesController extends Controller
{
    public function __invoke(Request $request, ListWorkspaces $listWorkspaces): JsonResponse
    {
        Gate::authorize('accessOwnerPortal', Workspace::class);
        Gate::authorize('viewAny', Workspace::class);

        /** @var User $user */
        $user = $request->user();

        return ApiResponse::resource(WorkspaceResource::collection($listWorkspaces->handle(
            user: $user,
            perPage: min(max($request->integer('per_page', 25), 1), 100),
            roles: Portal::rolesFor(Portal::OWNER),
        )));
    }
}
