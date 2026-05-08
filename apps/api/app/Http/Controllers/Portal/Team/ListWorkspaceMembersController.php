<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Team;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Team\Concerns\ResolvesWorkspaceTeamContext;
use App\Http\Resources\Workspaces\WorkspaceMemberResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Models\WorkspaceMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ListWorkspaceMembersController extends Controller
{
    use ResolvesWorkspaceTeamContext;

    public function __invoke(Request $request, int $workspace): JsonResponse
    {
        $this->authorizePortalAccess($request);

        /** @var User $user */
        $user = $request->user();

        $this->assertWorkspaceAccess($user, $workspace, $request);

        Gate::authorize('viewAny', [WorkspaceMember::class, $workspace]);

        return ApiResponse::resource(WorkspaceMemberResource::collection(
            WorkspaceMember::query()
                ->where('workspace_id', $workspace)
                ->with('user')
                ->orderBy('id')
                ->paginate(min(max($request->integer('per_page', 25), 1), 100))
                ->withQueryString(),
        ));
    }
}
