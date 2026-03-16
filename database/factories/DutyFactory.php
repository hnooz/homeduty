<?php

namespace Database\Factories;

use App\Enums\DutyFrequency;
use App\Models\Duty;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Duty>
 */
class DutyFactory extends Factory
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
            'assigned_user_id' => null,
            'name' => fake()->randomElement(['Dishes', 'Laundry', 'Vacuuming', 'Trash run']),
            'description' => fake()->sentence(),
            'frequency' => fake()->randomElement(DutyFrequency::cases()),
            'starts_on' => fake()->dateTimeBetween('today', '+10 days')->format('Y-m-d'),
        ];
    }
}
