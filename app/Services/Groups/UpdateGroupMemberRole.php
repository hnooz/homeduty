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

        $membership->user->syncRoles($role->toHomeDutyRole());
        $membership->user->forceFill([
            'is_group_admin' => $role->toHomeDutyRole() === HomeDutyRole::GroupAdmin,
        ])->saveQuietly();

        return $membership->refresh();
    }
}
