<?php

namespace App\Services\Groups;

use App\Enums\GroupMemberRole;
use App\Enums\HomeDutyRole;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SyncUserGroupMembership
{
    public function __construct(private RemoveGroupMember $removeGroupMember) {}

    public function handle(User $user, ?int $groupId, ?GroupMemberRole $role): void
    {
        DB::transaction(function () use ($user, $groupId, $role): void {
            $existing = $user->groupMemberships()->get();

            foreach ($existing as $membership) {
                if ($groupId !== null && $membership->group_id === $groupId) {
                    continue;
                }

                $this->removeGroupMember->handle($membership);
            }

            if ($groupId === null) {
                return;
            }

            $group = Group::query()->findOrFail($groupId);
            $effectiveRole = $role ?? GroupMemberRole::Member;

            $membership = $group->memberships()->updateOrCreate(
                ['user_id' => $user->id],
                ['role' => $effectiveRole],
            );

            $this->syncRoles($membership);
        });
    }

    private function syncRoles(GroupMember $membership): void
    {
        $user = $membership->user;
        $newRole = $membership->role->toHomeDutyRole();

        foreach ([HomeDutyRole::GroupAdmin, HomeDutyRole::GroupMember] as $groupRole) {
            if ($groupRole !== $newRole && $user->hasRole($groupRole->value)) {
                $user->removeRole($groupRole->value);
            }
        }

        $user->assignRole($newRole->value);

        $user->forceFill([
            'is_group_admin' => $user->hasRole(HomeDutyRole::SuperAdmin->value)
                || $newRole === HomeDutyRole::GroupAdmin,
        ])->saveQuietly();
    }
}
