<?php

declare(strict_types=1);

namespace App\Http\Resources\AuditAnalytics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogEntryResource extends JsonResource
{
    /**
     * @var list<string>
     */
    private const SENSITIVE_KEY_PATTERNS = [
        'token',
        'secret',
        'password',
        'body',
        'content',
        'email',
        'cookie',
        'authorization',
    ];

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $actor = $this->whenLoaded('actor');

        return [
            'id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'actor' => $actor ? [
                'id' => $actor->id,
                'name' => $actor->name,
                'email' => $actor->email,
            ] : null,
            'action' => $this->action,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'metadata' => $this->sanitizeMetadata($this->metadata_json),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    /**
     * @param  mixed  $metadata
     * @return array<string, mixed>
     */
    private function sanitizeMetadata(mixed $metadata): array
    {
        if (! is_array($metadata)) {
            return [];
        }

        $safe = [];

        foreach ($metadata as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            if ($this->containsSensitivePattern($key)) {
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $safe[$key] = $value;

                continue;
            }

            if (is_array($value)) {
                $safe[$key] = $this->sanitizeMetadata($value);
            }
        }

        return $safe;
    }

    private function containsSensitivePattern(string $key): bool
    {
        $key = strtolower($key);

        foreach (self::SENSITIVE_KEY_PATTERNS as $pattern) {
            if (str_contains($key, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
