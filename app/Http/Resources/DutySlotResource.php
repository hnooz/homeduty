<?php

namespace App\Http\Resources;

use App\Models\DutySlot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DutySlot
 */
class DutySlotResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'duty_id' => $this->duty_id,
            'user_id' => $this->user_id,
            'date' => $this->date?->toDateString(),
            'notified_same_day' => (bool) $this->notified_same_day,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
