<?php

declare(strict_types=1);

namespace App\Http\Requests\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
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
            'customer_name' => ['sometimes', 'required', 'string', 'max:120'],
            'customer_email' => ['sometimes', 'required', 'email', 'max:255'],
            'subject' => ['sometimes', 'required', 'string', 'max:255'],
            'body' => ['sometimes', 'required', 'string'],
        ];
    }
}
