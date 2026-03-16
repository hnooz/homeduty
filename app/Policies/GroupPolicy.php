<?php

namespace App\Policies;

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
        return $user->is_group_admin && ! $user->ownedGroup()->exists();
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
