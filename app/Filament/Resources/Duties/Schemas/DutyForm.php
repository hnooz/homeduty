<?php

namespace App\Filament\Resources\Duties\Schemas;

use App\Enums\DutyType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class DutyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options(DutyType::class)
                    ->live()
                    ->required(),
                Select::make('group_id')
                    ->label('Group')
                    ->relationship('group', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('starts_on')
                    ->required(),
                Select::make('cleaning_period_days')
                    ->label('Cleaning period (days)')
                    ->options([1 => 'Every 1 day', 2 => 'Every 2 days', 3 => 'Every 3 days'])
                    ->visible(fn (Get $get): bool => $get('type') === DutyType::Cleaning->value)
                    ->required(fn (Get $get): bool => $get('type') === DutyType::Cleaning->value),
            ]);
    }
}
