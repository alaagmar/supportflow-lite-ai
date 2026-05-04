<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class PortalAccessDeniedException extends Exception
{
    public function __construct()
    {
        parent::__construct('This account does not have access to that portal.');
    }

    public function render(Request $request): JsonResponse
    {
        return ApiResponse::error($this->getMessage(), Response::HTTP_FORBIDDEN);
    }
}
