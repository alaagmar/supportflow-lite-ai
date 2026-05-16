<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff\Auth;

use App\Domain\Identity\Portal;
use App\Domain\Identity\Support\PendingInvitationActivationGuard;
use App\Domain\Identity\UseCases\AuthenticatePortalUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StaffLoginRequest;
use App\Http\Resources\Identity\AuthSessionResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __invoke(
        StaffLoginRequest $request,
        AuthenticatePortalUser $authenticate,
        PendingInvitationActivationGuard $pendingActivationGuard,
    ): JsonResponse {
        /** @var array{email: string, password: string} $validated */
        $validated = $request->validated();

        $pendingActivationGuard->assertNoPendingActivation($validated['email']);

        return ApiResponse::resource(new AuthSessionResource($authenticate->handle(
            email: $validated['email'],
            password: $validated['password'],
            portal: Portal::STAFF,
        )));
    }
}
