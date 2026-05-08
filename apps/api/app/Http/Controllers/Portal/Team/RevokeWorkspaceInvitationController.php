<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Team;

use App\Domain\Workspaces\UseCases\RevokeWorkspaceInvitation;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Team\Concerns\ResolvesWorkspaceTeamContext;
use App\Http\Requests\Portal\Team\RevokeInvitationRequest;
use App\Http\Resources\Workspaces\WorkspaceInvitationResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Models\WorkspaceInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class RevokeWorkspaceInvitationController extends Controller
{
    use ResolvesWorkspaceTeamContext;

    public function __invoke(
        RevokeInvitationRequest $request,
        RevokeWorkspaceInvitation $revokeWorkspaceInvitation,
        int $workspace,
        int $invitation,
    ): JsonResponse {
        $this->authorizePortalAccess($request);

        /** @var User $user */
        $user = $request->user();

        $this->assertWorkspaceAccess($user, $workspace, $request);

        $workspaceInvitation = WorkspaceInvitation::query()
            ->where('workspace_id', $workspace)
            ->findOrFail($invitation);

        Gate::authorize('revoke', $workspaceInvitation);

        $workspaceInvitation = $revokeWorkspaceInvitation->handle($user, $workspace, $invitation);

        return ApiResponse::resource(new WorkspaceInvitationResource($workspaceInvitation));
    }
}
