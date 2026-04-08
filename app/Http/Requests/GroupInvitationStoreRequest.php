<?php

namespace App\Http\Requests;

use App\Enums\GroupMemberRole;
use App\Models\Group;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class GroupInvitationStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $group = $this->route('group');

        return $group instanceof Group
            && ($this->user()?->can('manageMembers', $group) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,filter', 'max:255'],
            'phone_number' => ['nullable', 'string', 'min:9', 'max:25', 'regex:/^\+?[0-9][0-9\s\-()]{6,24}$/'],
            'role' => ['required', new Enum(GroupMemberRole::class)],
        ];
    }
}
