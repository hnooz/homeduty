<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\HomeDutyRole;
use App\Models\User;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
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
                Hidden::make('email_verified_at'),
                Toggle::make('email_verified')
                    ->label('Email verified')
                    ->default(fn (?User $record): bool => filled($record?->email_verified_at))
                    ->dehydrated(false)
                    ->live()
                    ->afterStateUpdated(function (Set $set, bool $state): void {
                        $set('email_verified_at', $state ? now()->toDateTimeString() : null);
                    })
                    ->visible(fn (string $operation): bool => $operation === 'create'
                        || Auth::user()?->hasRole(HomeDutyRole::SuperAdmin->value)
                    ),
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
