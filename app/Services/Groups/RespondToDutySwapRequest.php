<?php

namespace App\Services\Groups;

use App\Enums\DutySwapRequestStatus;
use App\Models\DutySwapRequest;
use App\Notifications\DutySwapRespondedNotification;
use Illuminate\Support\Facades\DB;

class RespondToDutySwapRequest
{
    public function accept(DutySwapRequest $swapRequest): void
    {
        DB::transaction(function () use ($swapRequest): void {
            $swapRequest->update([
                'status' => DutySwapRequestStatus::Accepted,
                'responded_at' => now(),
            ]);

            $swapRequest->dutySlot->update([
                'user_id' => $swapRequest->recipient_id,
            ]);
        });

        $swapRequest->load(['dutySlot.duty', 'recipient']);
        $swapRequest->requester->notify(new DutySwapRespondedNotification($swapRequest));
    }

    public function reject(DutySwapRequest $swapRequest): void
    {
        $swapRequest->update([
            'status' => DutySwapRequestStatus::Rejected,
            'responded_at' => now(),
        ]);

        $swapRequest->load(['dutySlot.duty', 'recipient']);
        $swapRequest->requester->notify(new DutySwapRespondedNotification($swapRequest));
    }
}
