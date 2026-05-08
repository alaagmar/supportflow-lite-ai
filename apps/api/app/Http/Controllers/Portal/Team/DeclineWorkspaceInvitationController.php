<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Team;

use App\Domain\Workspaces\UseCases\DeclineWorkspaceInvitation;
use App\Http\Controllers\Controller;
use App\Http\Resources\Workspaces\WorkspaceInvitationResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Models\WorkspaceInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DeclineWorkspaceInvitationController extends Controller
{
    public function __invoke(
        Request $request,
        DeclineWorkspaceInvitation $declineWorkspaceInvitation,
        int $workspace,
        int $invitation,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $workspaceInvitation = WorkspaceInvitation::query()
            ->where('workspace_id', $workspace)
            ->findOrFail($invitation);

        Gate::authorize('respond', $workspaceInvitation);

        $workspaceInvitation = $declineWorkspaceInvitation->handle($user, $workspace, $invitation);

        return ApiResponse::resource(new WorkspaceInvitationResource($workspaceInvitation));
    }
}
