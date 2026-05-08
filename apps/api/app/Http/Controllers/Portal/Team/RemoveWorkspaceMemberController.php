<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Team;

use App\Domain\Workspaces\UseCases\RemoveWorkspaceMember;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Team\Concerns\ResolvesWorkspaceTeamContext;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Models\WorkspaceMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RemoveWorkspaceMemberController extends Controller
{
    use ResolvesWorkspaceTeamContext;

    public function __invoke(
        Request $request,
        RemoveWorkspaceMember $removeWorkspaceMember,
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

        Gate::authorize('delete', $target);

        $removeWorkspaceMember->handle($user, $workspace, $member);

        return ApiResponse::noContent();
    }
}
