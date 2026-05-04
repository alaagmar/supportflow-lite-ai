<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

final class ApiResponse
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function success(array $data = [], int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json($data, $status);
    }

    public static function resource(JsonResource $resource, int $status = Response::HTTP_OK): JsonResponse
    {
        return $resource->response()->setStatusCode($status);
    }

    public static function noContent(): JsonResponse
    {
        return response()->json(status: Response::HTTP_NO_CONTENT);
    }

    /**
     * @param  array<string, list<string>>|null  $errors
     */
    public static function error(string $message, int $status, ?array $errors = null): JsonResponse
    {
        return response()->json(array_filter([
            'message' => $message,
            'errors' => $errors,
        ], fn (mixed $value): bool => $value !== null), $status);
    }
}
