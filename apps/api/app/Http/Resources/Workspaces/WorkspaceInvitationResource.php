<?php

declare(strict_types=1);

namespace App\Http\Resources\Workspaces;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkspaceInvitationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'invited_email' => $this->invited_email,
            'invited_role' => $this->invited_role,
            'status' => $this->status,
            'invited_by_user_id' => $this->invited_by_user_id,
            'accepted_by_user_id' => $this->accepted_by_user_id,
            'accepted_at' => $this->accepted_at?->toISOString(),
            'declined_at' => $this->declined_at?->toISOString(),
            'revoked_at' => $this->revoked_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
