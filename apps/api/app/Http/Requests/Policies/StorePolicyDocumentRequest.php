<?php

declare(strict_types=1);

namespace App\Http\Requests\Policies;

use Illuminate\Foundation\Http\FormRequest;

class StorePolicyDocumentRequest extends FormRequest
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
        if ($this->isMethod('get')) {
            return [
                'status' => ['sometimes', 'string', 'in:active,archived'],
                'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            ];
        }

        return [
            'title' => ['required', 'string', 'max:255'],
            'content_text' => ['required', 'string'],
            'type' => ['sometimes', 'string', 'max:32'],
        ];
    }
}
