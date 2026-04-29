<?php

namespace App\Http\Resources;

use App\Models\DutySwapRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DutySwapRequest
 */
class DutySwapRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'duty_slot_id' => $this->duty_slot_id,
            'requester' => new UserResource($this->whenLoaded('requester')),
            'recipient' => new UserResource($this->whenLoaded('recipient')),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'message' => $this->message,
            'duty_slot' => new DutySlotResource($this->whenLoaded('dutySlot')),
            'responded_at' => $this->responded_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
