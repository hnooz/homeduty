<?php

namespace App\Filament\Resources\Duties\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DutyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('group.name')->label('Group'),
            TextEntry::make('type')->badge(),
            TextEntry::make('starts_on')->date(),
            TextEntry::make('cleaning_period_days')->label('Cleaning period (days)')->placeholder('—'),
            TextEntry::make('members_count')
                ->label('Members')
                ->state(fn ($record) => $record->members()->count()),
            TextEntry::make('slots_count')
                ->label('Slots')
                ->state(fn ($record) => $record->slots()->count()),
            TextEntry::make('created_at')->dateTime(),
            TextEntry::make('updated_at')->dateTime(),
        ]);
    }
}
