<?php

namespace App\Filament\Resources\GroupInvitations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GroupInvitationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Invitee')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('group.name')
                    ->label('Group')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->badge()
                    ->sortable(),
                TextColumn::make('invitedBy.name')
                    ->label('Invited By')
                    ->sortable(),
                TextColumn::make('accepted_at')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state ? 'Accepted' : 'Pending')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'warning')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Sent')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
