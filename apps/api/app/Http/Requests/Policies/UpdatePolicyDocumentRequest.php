<?php

declare(strict_types=1);

namespace App\Http\Requests\Policies;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePolicyDocumentRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:255'],
            'content_text' => ['sometimes', 'string'],
            'type' => ['sometimes', 'string', 'max:32'],
        ];
    }
}
