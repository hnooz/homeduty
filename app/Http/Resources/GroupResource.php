<?php

namespace App\Http\Resources;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Group
 */
class GroupResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'owner_id' => $this->owner_id,
            'is_owner' => $request->user()?->id === $this->owner_id,
            'invite_url' => $request->user()?->can('manageMembers', $this->resource)
                ? $this->inviteUrl()
                : null,
            'members_count' => $this->when(
                isset($this->members_count),
                fn () => (int) $this->members_count,
            ),
            'pending_invitations_count' => $this->when(
                isset($this->pending_invitations_count),
                fn () => (int) $this->pending_invitations_count,
            ),
            'duties_count' => $this->when(
                isset($this->duties_count),
                fn () => (int) $this->duties_count,
            ),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'members' => GroupMemberResource::collection($this->whenLoaded('memberships')),
            'duties' => DutyResource::collection($this->whenLoaded('duties')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
