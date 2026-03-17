<?php

namespace App\Services\Groups;

use App\Models\Duty;

class UpdateGroupDuty
{
    /**
     * @param  array{name: string, description?: string|null, frequency: string, starts_on: string, assigned_user_id?: int|null}  $attributes
     */
    public function handle(Duty $duty, array $attributes): Duty
    {
        $duty->forceFill([
            'assigned_user_id' => $attributes['assigned_user_id'] ?? null,
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'frequency' => $attributes['frequency'],
            'starts_on' => $attributes['starts_on'],
        ])->save();

        return $duty->refresh()->load('assignedUser');
    }
}
