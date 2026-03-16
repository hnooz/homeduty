<?php

namespace App\Services\Groups;

use App\Models\Duty;
use App\Models\Group;

class CreateGroupDuty
{
    /**
     * @param  array{name: string, description?: string|null, frequency: string, starts_on: string, assigned_user_id?: int|null}  $attributes
     */
    public function handle(Group $group, array $attributes): Duty
    {
        /** @var Duty $duty */
        $duty = $group->duties()->create([
            'assigned_user_id' => $attributes['assigned_user_id'] ?? null,
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'frequency' => $attributes['frequency'],
            'starts_on' => $attributes['starts_on'],
        ]);

        return $duty->load('assignedUser');
    }
}
