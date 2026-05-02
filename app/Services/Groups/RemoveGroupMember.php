<?php

namespace App\Services\Groups;

use App\Enums\HomeDutyRole;
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

        GroupMember::query()->whereKey($membership->id)->delete();

        if (! $user->ownedGroup()->exists() && ! $user->groupMemberships()->exists()) {
            if ($user->hasRole(HomeDutyRole::SuperAdmin->value)) {
                foreach ([HomeDutyRole::GroupOwner, HomeDutyRole::GroupAdmin, HomeDutyRole::GroupMember] as $groupRole) {
                    if ($user->hasRole($groupRole->value)) {
                        $user->removeRole($groupRole->value);
                    }
                }
            } else {
                $user->syncRoles([]);
            }

            $user->forceFill([
                'is_group_admin' => false,
            ])->saveQuietly();
        }
    }
}
