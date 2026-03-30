<?php

namespace App\Services\Groups;

use App\Enums\GroupMemberRole;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateHomeGroup
{
    /**
     * @param  array{name: string}  $attributes
     */
    public function handle(User $owner, array $attributes): Group
    {
        /** @var Group $group */
        $group = DB::transaction(function () use ($owner, $attributes): Group {
            $group = Group::query()->create([
                'name' => trim($attributes['name']),
                'owner_id' => $owner->id,
            ]);

            $group->memberships()->create([
                'user_id' => $owner->id,
                'role' => GroupMemberRole::Admin,
            ]);

            return $group;
        });

        return $group->load(['owner', 'memberships']);
    }
}
