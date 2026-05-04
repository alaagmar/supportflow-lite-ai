<?php

declare(strict_types=1);

namespace App\Http\Resources\Identity;

use App\Domain\Identity\Data\AuthSessionData;
use App\Http\Resources\Workspaces\WorkspaceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthSessionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var AuthSessionData $session */
        $session = $this->resource;

        return [
            'token' => $session->token,
            'token_type' => 'Bearer',
            'portal' => $session->portal,
            'user' => (new UserResource($session->user))->resolve($request),
            'workspaces' => WorkspaceResource::collection($session->workspaces)->resolve($request),
        ];
    }
}
