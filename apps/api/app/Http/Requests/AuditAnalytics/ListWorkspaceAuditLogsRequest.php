<?php

declare(strict_types=1);

namespace App\Http\Requests\AuditAnalytics;

use Illuminate\Foundation\Http\FormRequest;

class ListWorkspaceAuditLogsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'start_at' => ['sometimes', 'date'],
            'end_at' => ['sometimes', 'date', 'after_or_equal:start_at'],
            'action' => ['sometimes', 'string', 'max:120'],
            'actor_user_id' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
