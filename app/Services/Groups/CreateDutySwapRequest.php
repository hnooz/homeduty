<?php

namespace App\Services\Groups;

use App\Enums\DutySwapRequestStatus;
use App\Models\DutySwapRequest;
use App\Models\User;
use App\Notifications\DutySwapRequestedNotification;

class CreateDutySwapRequest
{
    /**
     * @param  array{duty_slot_id: int, recipient_id: int, message?: string|null}  $attributes
     */
    public function handle(User $requester, array $attributes): DutySwapRequest
    {
        $swapRequest = DutySwapRequest::query()->create([
            'duty_slot_id' => $attributes['duty_slot_id'],
            'requester_id' => $requester->id,
            'recipient_id' => $attributes['recipient_id'],
            'status' => DutySwapRequestStatus::Pending,
            'message' => $attributes['message'] ?? null,
        ]);

        $swapRequest->load(['dutySlot.duty', 'requester']);

        $recipient = User::findOrFail($attributes['recipient_id']);
        $recipient->notify(new DutySwapRequestedNotification($swapRequest));

        return $swapRequest;
    }
}
