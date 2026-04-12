<?php

namespace App\Http\Resources;

use App\Models\Duty;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Duty
 */
class DutyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'group_id' => $this->group_id,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'type_icon' => $this->type->icon(),
            'starts_on' => $this->starts_on?->toDateString(),
            'cleaning_period_days' => $this->cleaning_period_days,
            'members' => UserResource::collection($this->whenLoaded('members')),
            'slots' => DutySlotResource::collection($this->whenLoaded('slots')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
