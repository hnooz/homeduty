<?php

namespace App\Policies;

use App\Enums\HomeDutyPermission;
use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->groupMemberships()->exists() || $user->ownedGroup()->exists();
    }

    public function view(User $user, Group $group): bool
    {
        return $group->owner_id === $user->id || $group->members()->whereKey($user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->can(HomeDutyPermission::CreateHomeGroup->value) && ! $user->ownedGroup()->exists();
    }

    public function viewMembers(User $user, Group $group): bool
    {
        return $this->view($user, $group);
    }

    public function manageMembers(User $user, Group $group): bool
    {
        return $group->owner_id === $user->id && $user->can(HomeDutyPermission::ManageHomeGroupMembers->value);
    }

    public function update(User $user, Group $group): bool
    {
        return $group->owner_id === $user->id;
    }

    public function delete(User $user, Group $group): bool
    {
        return $group->owner_id === $user->id;
    }

    public function restore(User $user, Group $group): bool
    {
        return false;
    }

    public function forceDelete(User $user, Group $group): bool
    {
        return false;
    }
}
