<?php

namespace App\Policies;

use App\Enums\HomeDutyPermission;
use App\Models\User;

class UserPolicy
{
    public function createHomeGroup(User $user): bool
    {
        return $user->can(HomeDutyPermission::CreateHomeGroup->value);
    }
}
