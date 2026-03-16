<?php

namespace App\Services\Groups;

use App\Models\GroupMember;
use Illuminate\Validation\ValidationException;

class RemoveGroupMember
{
    public function handle(GroupMember $membership): void
    {
        if ($membership->group->owner_id === $membership->user_id) {
            throw ValidationException::withMessages([
                'member' => 'The Home Group owner cannot be removed.',
            ]);
        }

        $user = $membership->user;

        $membership->delete();

        if (! $user->ownedGroup()->exists() && ! $user->groupMemberships()->exists()) {
            $user->syncRoles([]);
            $user->forceFill([
                'is_group_admin' => false,
            ])->saveQuietly();
        }
    }
}
