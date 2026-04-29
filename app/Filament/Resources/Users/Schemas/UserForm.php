<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\HomeDutyRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

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
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->rule(Password::default())
                    ->confirmed()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->visible(fn (string $operation): bool => $operation === 'create'
                        || Auth::user()?->hasRole(HomeDutyRole::SuperAdmin->value)
                    ),
                TextInput::make('password_confirmation')
                    ->password()
                    ->revealable()
                    ->requiredWith('password')
                    ->dehydrated(false)
                    ->visible(fn (string $operation): bool => $operation === 'create'
                        || Auth::user()?->hasRole(HomeDutyRole::SuperAdmin->value)
                    ),
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
