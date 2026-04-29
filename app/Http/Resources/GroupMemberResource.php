<?php

namespace App\Http\Resources;

use App\Models\GroupMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin GroupMember
 */
class GroupMemberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'group_id' => $this->group_id,
            'user_id' => $this->user_id,
            'role' => $this->role->value,
            'role_label' => $this->role->label(),
            'is_owner' => $this->whenLoaded(
                'group',
                fn (): bool => $this->group->owner_id === $this->user_id,
            ),
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
