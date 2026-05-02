<?php

namespace App\Filament\Resources\Groups\RelationManagers;

use App\Enums\GroupMemberRole;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class InvitationsRelationManager extends RelationManager
{
    protected static string $relationship = 'invitations';

    protected static ?string $title = 'Invitations';

    protected static ?string $recordTitleAttribute = 'email';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('email')->email()->required()->maxLength(255),
            Select::make('role')
                ->options(GroupMemberRole::class)
                ->default(GroupMemberRole::Member->value)
                ->required(),
            DateTimePicker::make('expires_at')->label('Expires at'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('role')->badge(),
                TextColumn::make('invitedBy.name')->label('Invited by'),
                TextColumn::make('accepted_at')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state ? 'Accepted' : 'Pending')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'warning'),
                TextColumn::make('expires_at')->dateTime()->placeholder('—'),
                TextColumn::make('created_at')->label('Sent')->dateTime()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('accepted_at')
                    ->label('Accepted')
                    ->nullable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $data['invited_by_user_id'] = auth()->id();
                        $data['token'] = (string) Str::uuid();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->label('Revoke'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
