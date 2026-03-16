<?php

namespace App\Http\Requests;

use App\Enums\DutyFrequency;
use App\Models\Group;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class GroupDutyStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $group = $this->route('group');

        return $group instanceof Group
            && ($this->user()?->can('manageDuties', $group) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Group|null $group */
        $group = $this->route('group');

        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'frequency' => ['required', new Enum(DutyFrequency::class)],
            'starts_on' => ['required', 'date'],
            'assigned_user_id' => [
                'nullable',
                'integer',
                Rule::exists('group_members', 'user_id')->where(fn ($query) => $query->where('group_id', $group?->id)),
            ],
        ];
    }
}
