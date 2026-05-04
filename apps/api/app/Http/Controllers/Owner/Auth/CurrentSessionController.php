<?php

declare(strict_types=1);

namespace App\Http\Controllers\Owner\Auth;

use App\Domain\Identity\Portal;
use App\Domain\Identity\UseCases\CurrentSession;
use App\Http\Controllers\Controller;
use App\Http\Resources\Identity\CurrentSessionResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrentSessionController extends Controller
{
    public function __invoke(Request $request, CurrentSession $currentSession): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return ApiResponse::resource(new CurrentSessionResource($currentSession->handle($user, Portal::OWNER)));
    }
}
