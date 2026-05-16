<?php

declare(strict_types=1);

namespace App\Http\Requests\AuditAnalytics;

use Illuminate\Foundation\Http\FormRequest;

class GetWorkspaceAnalyticsSummaryRequest extends FormRequest
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
        ];
    }
}
