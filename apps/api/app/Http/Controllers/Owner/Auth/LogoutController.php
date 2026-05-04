<?php

declare(strict_types=1);

namespace App\Http\Controllers\Owner\Auth;

use App\Domain\Identity\UseCases\LogoutSession;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __invoke(Request $request, LogoutSession $logoutSession): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user !== null) {
            $logoutSession->handle($user);
        }

        return ApiResponse::noContent();
    }
}
