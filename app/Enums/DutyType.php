<?php

namespace App\Enums;

enum DutyType: string
{
    case Cooking = 'cooking';
    case Cleaning = 'cleaning';

    public function label(): string
    {
        return match ($this) {
            self::Cooking => 'Cooking',
            self::Cleaning => 'Cleaning',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Cooking => '🍳',
            self::Cleaning => '🧹',
        };
    }

    /**
     * Gap in days between rotation slots for this duty type.
     *
     * @return array{min: int, max: int}
     */
    public function gapDays(): array
    {
        return match ($this) {
            self::Cooking => ['min' => 1, 'max' => 1],
            self::Cleaning => ['min' => 2, 'max' => 3],
        };
    }
}
