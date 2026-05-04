<?php

declare(strict_types=1);

namespace App\Http\Resources\Identity;

use App\Domain\Identity\Data\CurrentSessionData;
use App\Http\Resources\Workspaces\WorkspaceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrentSessionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var CurrentSessionData $session */
        $session = $this->resource;

        return [
            'user' => (new UserResource($session->user))->resolve($request),
            'workspaces' => WorkspaceResource::collection($session->workspaces)->resolve($request),
        ];
    }
}
