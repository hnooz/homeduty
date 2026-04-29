<?php

namespace App\Http\Requests;

use App\Enums\DutySwapRequestStatus;
use App\Models\DutySlot;
use App\Models\DutySwapRequest;
use App\Models\Group;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DutySwapRequestStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $group = $this->route('group');

        return $group instanceof Group
            && ($this->user()?->can('view', $group) ?? false);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Group|null $group */
        $group = $this->route('group');
        $user = $this->user();

        return [
            'duty_slot_id' => [
                'required',
                'integer',
                Rule::exists('duty_slots', 'id')->where(fn ($query) => $query
                    ->where('user_id', $user?->id)
                    ->where('date', '>=', now()->toDateString())
                ),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $hasPending = DutySwapRequest::query()
                        ->where('duty_slot_id', $value)
                        ->where('status', DutySwapRequestStatus::Pending)
                        ->exists();

                    if ($hasPending) {
                        $fail('A pending swap request already exists for this duty slot.');
                    }
                },
            ],
            'recipient_id' => [
                'required',
                'integer',
                'different:'.$user?->id,
                Rule::exists('group_members', 'user_id')->where(fn ($query) => $query->where('group_id', $group?->id)),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $dutySlot = DutySlot::find($this->input('duty_slot_id'));

                    if (! $dutySlot) {
                        return;
                    }

                    $isInRotation = $dutySlot->duty->members()->whereKey($value)->exists();

                    if (! $isInRotation) {
                        $fail('The recipient must be in the same duty rotation.');
                    }
                },
            ],
            'message' => ['nullable', 'string', 'max:500'],
        ];
    }
}
