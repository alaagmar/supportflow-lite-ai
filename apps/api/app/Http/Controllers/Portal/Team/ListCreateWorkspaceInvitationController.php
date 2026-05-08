<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Team;

use App\Domain\Workspaces\UseCases\InviteWorkspaceMember;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Team\Concerns\ResolvesWorkspaceTeamContext;
use App\Http\Requests\Portal\Team\CreateInvitationRequest;
use App\Http\Resources\Workspaces\WorkspaceInvitationResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Models\WorkspaceInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ListCreateWorkspaceInvitationController extends Controller
{
    use ResolvesWorkspaceTeamContext;

    public function __invoke(
        CreateInvitationRequest $request,
        InviteWorkspaceMember $inviteWorkspaceMember,
        int $workspace,
    ): JsonResponse {
        $this->authorizePortalAccess($request);

        /** @var User $user */
        $user = $request->user();

        if ($request->isMethod('get')) {
            if ((string) $request->route('portal') === 'staff') {
                return ApiResponse::resource(WorkspaceInvitationResource::collection(
                    WorkspaceInvitation::query()
                        ->where('workspace_id', $workspace)
                        ->whereRaw('LOWER(invited_email) = ?', [mb_strtolower($user->email)])
                        ->latest('id')
                        ->paginate(min(max($request->integer('per_page', 25), 1), 100))
                        ->withQueryString(),
                ));
            }

            $this->assertWorkspaceAccess($user, $workspace, $request);
            Gate::authorize('viewAny', [WorkspaceInvitation::class, $workspace]);

            return ApiResponse::resource(WorkspaceInvitationResource::collection(
                WorkspaceInvitation::query()
                    ->where('workspace_id', $workspace)
                    ->latest('id')
                    ->paginate(min(max($request->integer('per_page', 25), 1), 100))
                    ->withQueryString(),
            ));
        }

        $this->assertWorkspaceAccess($user, $workspace, $request);

        Gate::authorize('create', [WorkspaceInvitation::class, $workspace]);

        /** @var array{email: string, role: string} $validated */
        $validated = $request->validated();

        $invitation = $inviteWorkspaceMember->handle($user, $workspace, $validated['email'], $validated['role']);

        return ApiResponse::resource(new WorkspaceInvitationResource($invitation), Response::HTTP_CREATED);
    }
}
