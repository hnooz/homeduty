<?php

namespace App\Models;

use App\Enums\DutySwapRequestStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['duty_slot_id', 'requester_id', 'recipient_id', 'status', 'message', 'responded_at'])]
class DutySwapRequest extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => DutySwapRequestStatus::class,
            'responded_at' => 'datetime',
        ];
    }

    public function dutySlot(): BelongsTo
    {
        return $this->belongsTo(DutySlot::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function isPending(): bool
    {
        return $this->status === DutySwapRequestStatus::Pending;
    }
}
