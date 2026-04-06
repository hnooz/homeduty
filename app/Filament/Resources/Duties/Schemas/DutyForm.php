<?php

namespace App\Filament\Resources\Duties\Schemas;

use App\Enums\DutyType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class DutyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options(DutyType::class)
                    ->required(),
                Select::make('group_id')
                    ->label('Group')
                    ->relationship('group', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('starts_on')
                    ->required(),
            ]);
    }
}
