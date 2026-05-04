<?php

declare(strict_types=1);

namespace App\Http\Resources\Tickets;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiRunResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'ticket_id' => $this->ticket_id,
            'provider' => $this->provider,
            'model' => $this->model,
            'task_type' => $this->task_type,
            'status' => $this->status,
            'error_message' => $this->error_message,
            'latency_ms' => $this->latency_ms,
            'confidence' => $this->confidence,
            'prompt_version' => $this->prompt_version,
            'started_at' => $this->started_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
