<?php

declare(strict_types=1);

namespace App\Http\Requests\Policies;

use Illuminate\Foundation\Http\FormRequest;

class RetrievePolicyGuidanceRequest extends FormRequest
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
            'query_text' => ['required', 'string'],
            'category_hint' => ['sometimes', 'string', 'max:255'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:10'],
        ];
    }
}
