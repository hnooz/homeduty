<?php

namespace App\Filament\Resources\Groups\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class GroupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('owner.name')->label('Owner'),
                TextEntry::make('memberships_count')
                    ->label('Members')
                    ->state(fn ($record) => $record->memberships()->count()),
                TextEntry::make('duties_count')
                    ->label('Duties')
                    ->state(fn ($record) => $record->duties()->count()),
                TextEntry::make('invite_token')->label('Invite Token')->copyable(),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),
                TextEntry::make('deleted_at')->dateTime()->placeholder('—'),
            ]);
    }
}
