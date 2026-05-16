<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff\Auth;

use App\Domain\Workspaces\Support\WorkspaceInvitationActivationTokenService;
use App\Domain\Workspaces\UseCases\CompleteInvitationActivation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\CompleteInvitationActivationRequest;
use App\Http\Responses\ApiResponse;
use App\Models\WorkspaceMember;
use Illuminate\Http\JsonResponse;

class CompleteInvitationActivationController extends Controller
{
    public function __invoke(
        CompleteInvitationActivationRequest $request,
        CompleteInvitationActivation $completeInvitationActivation,
        WorkspaceInvitationActivationTokenService $activationTokens,
    ): JsonResponse {
        /** @var array{token: string, password: string, password_confirmation: string} $validated */
        $validated = $request->validated();

        // Resolve the portal before consuming the token so we can still read the invited_role.
        $activationToken = $activationTokens->findActiveByPlainToken($validated['token']);
        $invitedRole = $activationToken?->workspaceInvitation?->invited_role;

        $completeInvitationActivation->handle(
            token: $validated['token'],
            password: $validated['password'],
        );

        // Derive the login portal from the invited role so the frontend redirects correctly.
        // Admins must use the admin portal; agents and viewers use the staff portal.
        $portal = $invitedRole === WorkspaceMember::ROLE_ADMIN ? 'admin' : 'staff';

        return ApiResponse::success([
            'data' => [
                'message' => 'Activation completed. You can now sign in with your credentials.',
                'portal' => $portal,
            ],
        ]);
    }
}
