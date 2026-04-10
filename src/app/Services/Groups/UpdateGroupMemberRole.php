<?php

namespace App\Services\Groups;

use App\Enums\GroupMemberRole;
use App\Enums\HomeDutyRole;
use App\Models\GroupMember;
use Illuminate\Validation\ValidationException;

class UpdateGroupMemberRole
{
    public function handle(GroupMember $membership, GroupMemberRole $role): GroupMember
    {
        if ($membership->group->owner_id === $membership->user_id) {
            throw ValidationException::withMessages([
                'role' => 'The Home Group owner always remains an admin.',
            ]);
        }

        $membership->forceFill([
            'role' => $role,
        ])->save();

        $newRole = $role->toHomeDutyRole();

        foreach ([HomeDutyRole::GroupAdmin, HomeDutyRole::GroupMember] as $groupRole) {
            if ($groupRole !== $newRole && $membership->user->hasRole($groupRole->value)) {
                $membership->user->removeRole($groupRole->value);
            }
        }

        $membership->user->assignRole($newRole->value);

        $membership->user->forceFill([
            'is_group_admin' => $newRole === HomeDutyRole::GroupAdmin,
        ])->saveQuietly();

        return $membership->refresh();
    }
}
