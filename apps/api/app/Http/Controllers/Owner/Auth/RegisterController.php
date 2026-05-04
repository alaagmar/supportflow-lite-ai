<?php

declare(strict_types=1);

namespace App\Http\Controllers\Owner\Auth;

use App\Domain\Identity\UseCases\RegisterOwner;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\RegisterOwnerRequest;
use App\Http\Resources\Identity\AuthSessionResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class RegisterController extends Controller
{
    public function __invoke(RegisterOwnerRequest $request, RegisterOwner $registerOwner): JsonResponse
    {
        /** @var array{name: string, email: string, password: string, workspace_name: string} $validated */
        $validated = $request->validated();

        return ApiResponse::resource(new AuthSessionResource($registerOwner->handle(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
            workspaceName: $validated['workspace_name'],
        )), Response::HTTP_CREATED);
    }
}
