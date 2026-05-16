<?php

declare(strict_types=1);

namespace App\Http\Requests\AuditAnalytics;

use Illuminate\Foundation\Http\FormRequest;

class ListTicketAuditLogsRequest extends FormRequest
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
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
