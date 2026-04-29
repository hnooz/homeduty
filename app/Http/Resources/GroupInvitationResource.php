<?php

namespace App\Http\Resources;

use App\Models\GroupInvitation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin GroupInvitation
 */
class GroupInvitationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'token' => $this->token,
            'group_id' => $this->group_id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role->value,
            'role_label' => $this->role->label(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'accepted_at' => $this->accepted_at?->toIso8601String(),
            'is_pending' => $this->isPending(),
            'group' => new GroupResource($this->whenLoaded('group')),
            'invited_by' => new UserResource($this->whenLoaded('invitedBy')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
