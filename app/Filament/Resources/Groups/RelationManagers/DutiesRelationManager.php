<?php

namespace App\Filament\Resources\Groups\RelationManagers;

use App\Enums\DutyType;
use App\Filament\Resources\Duties\DutyResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DutiesRelationManager extends RelationManager
{
    protected static string $relationship = 'duties';

    protected static ?string $title = 'Duties';

    protected static ?string $recordTitleAttribute = 'type';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('type')
                ->options(DutyType::class)
                ->live()
                ->required(),
            DatePicker::make('starts_on')->required(),
            Select::make('cleaning_period_days')
                ->label('Cleaning period (days)')
                ->options([1 => 'Every 1 day', 2 => 'Every 2 days', 3 => 'Every 3 days'])
                ->visible(fn (Get $get): bool => $get('type') === DutyType::Cleaning->value)
                ->required(fn (Get $get): bool => $get('type') === DutyType::Cleaning->value),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                TextColumn::make('type')->badge()->sortable(),
                TextColumn::make('starts_on')->date()->sortable(),
                TextColumn::make('cleaning_period_days')->label('Period')->placeholder('—'),
                TextColumn::make('members_count')->label('Members')->counts('members'),
                TextColumn::make('slots_count')->label('Slots')->counts('slots'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => DutyResource::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->url(fn ($record) => DutyResource::getUrl('edit', ['record' => $record])),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
