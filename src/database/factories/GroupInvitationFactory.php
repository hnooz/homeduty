<?php

namespace Database\Factories;

use App\Enums\GroupMemberRole;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<GroupInvitation>
 */
class GroupInvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'invited_by_user_id' => User::factory(),
            'accepted_by_user_id' => null,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role' => fake()->randomElement(GroupMemberRole::cases()),
            'token' => (string) Str::uuid(),
            'expires_at' => now()->addDays(7),
            'accepted_at' => null,
        ];
    }
}
