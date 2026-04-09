<?php

namespace App\Filament\Resources\GroupInvitations\Schemas;

use App\Enums\GroupMemberRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GroupInvitationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Select::make('group_id')
                    ->label('Group')
                    ->relationship('group', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('role')
                    ->options(GroupMemberRole::class)
                    ->required(),
            ]);
    }
}
