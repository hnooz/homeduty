<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\HomeDutyRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
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
                Select::make('roles')
                    ->label('Roles')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->options(
                        collect(HomeDutyRole::cases())
                            ->mapWithKeys(fn (HomeDutyRole $role) => [$role->value => $role->label()])
                    )
                    ->preload(),
            ]);
    }
}
