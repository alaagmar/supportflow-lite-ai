<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Workspaces;

use App\Domain\Identity\Portal;
use App\Domain\Workspaces\UseCases\ShowWorkspace;
use App\Http\Controllers\Controller;
use App\Http\Resources\Workspaces\WorkspaceResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ShowWorkspaceController extends Controller
{
    public function __invoke(Request $request, ShowWorkspace $showWorkspace, int $workspace): JsonResponse
    {
        Gate::authorize('accessAdminPortal', Workspace::class);

        /** @var User $user */
        $user = $request->user();

        $workspaceModel = $showWorkspace->handle($user, $workspace, Portal::rolesFor(Portal::ADMIN));

        Gate::authorize('view', $workspaceModel);

        return ApiResponse::resource(new WorkspaceResource($workspaceModel));
    }
}
