<?php

namespace Database\Factories;

use App\Enums\DutyType;
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
            'type' => fake()->randomElement(DutyType::cases()),
            'starts_on' => fake()->dateTimeBetween('today', '+10 days')->format('Y-m-d'),
        ];
    }

    public function cooking(): static
    {
        return $this->state(['type' => DutyType::Cooking]);
    }

    public function cleaning(): static
    {
        return $this->state(['type' => DutyType::Cleaning]);
    }
}
