<?php

declare(strict_types=1);

namespace App\Http\Controllers\Owner\Auth;

use App\Domain\Identity\Portal;
use App\Domain\Identity\UseCases\AuthenticatePortalUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\OwnerLoginRequest;
use App\Http\Resources\Identity\AuthSessionResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __invoke(OwnerLoginRequest $request, AuthenticatePortalUser $authenticate): JsonResponse
    {
        /** @var array{email: string, password: string} $validated */
        $validated = $request->validated();

        return ApiResponse::resource(new AuthSessionResource($authenticate->handle(
            email: $validated['email'],
            password: $validated['password'],
            portal: Portal::OWNER,
        )));
    }
}
