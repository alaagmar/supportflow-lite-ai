<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Team;

use App\Domain\Workspaces\UseCases\UpdateWorkspaceMemberRole;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Team\Concerns\ResolvesWorkspaceTeamContext;
use App\Http\Requests\Portal\Team\UpdateWorkspaceMemberRoleRequest;
use App\Http\Resources\Workspaces\WorkspaceMemberResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Models\WorkspaceMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class UpdateWorkspaceMemberRoleController extends Controller
{
    use ResolvesWorkspaceTeamContext;

    public function __invoke(
        UpdateWorkspaceMemberRoleRequest $request,
        UpdateWorkspaceMemberRole $updateWorkspaceMemberRole,
        int $workspace,
        int $member,
    ): JsonResponse {
        $this->authorizePortalAccess($request);

        /** @var User $user */
        $user = $request->user();

        $this->assertWorkspaceAccess($user, $workspace, $request);

        $target = WorkspaceMember::query()
            ->where('workspace_id', $workspace)
            ->findOrFail($member);

        Gate::authorize('update', $target);

        /** @var array{role: string} $validated */
        $validated = $request->validated();

        $updated = $updateWorkspaceMemberRole->handle($user, $workspace, $member, $validated['role']);

        return ApiResponse::resource(new WorkspaceMemberResource($updated->load('user')));
    }
}
