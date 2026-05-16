<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff\Auth;

use App\Domain\Workspaces\UseCases\ResendInvitationActivation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ResendInvitationActivationRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ResendInvitationActivationController extends Controller
{
    public function __invoke(
        ResendInvitationActivationRequest $request,
        ResendInvitationActivation $resendInvitationActivation,
    ): JsonResponse {
        /** @var array{email: string, workspace_id: int} $validated */
        $validated = $request->validated();

        $resendInvitationActivation->handle(
            email: $validated['email'],
            workspaceId: $validated['workspace_id'],
        );

        return ApiResponse::success([
            'data' => [
                'message' => 'If a pending activation exists, a replacement email has been sent.',
            ],
        ], Response::HTTP_ACCEPTED);
    }
}
