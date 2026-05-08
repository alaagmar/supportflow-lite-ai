<?php

declare(strict_types=1);

namespace App\Http\Requests\Portal\Team;

use App\Models\WorkspaceMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkspaceMemberRoleRequest extends FormRequest
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
            'role' => ['required', 'string', Rule::in([
                WorkspaceMember::ROLE_ADMIN,
                WorkspaceMember::ROLE_AGENT,
                WorkspaceMember::ROLE_VIEWER,
            ])],
        ];
    }
}
