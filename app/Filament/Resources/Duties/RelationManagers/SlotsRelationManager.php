<?php

namespace App\Filament\Resources\Duties\RelationManagers;

use App\Models\Duty;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SlotsRelationManager extends RelationManager
{
    protected static string $relationship = 'slots';

    protected static ?string $title = 'Slots';

    protected static ?string $recordTitleAttribute = 'date';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('date')->required(),
            Select::make('user_id')
                ->label('Assigned user')
                ->required()
                ->searchable()
                ->preload()
                ->options(function (): array {
                    /** @var Duty $duty */
                    $duty = $this->getOwnerRecord();
                    $userIds = $duty->group->memberships()->pluck('user_id')->all();

                    return User::query()
                        ->whereIn('id', $userIds)
                        ->pluck('name', 'id')
                        ->all();
                }),
            Toggle::make('notified_same_day'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->columns([
                TextColumn::make('date')->date()->sortable(),
                TextColumn::make('user.name')->label('Assigned')->searchable(),
                IconColumn::make('notified_same_day')->boolean()->label('Same day'),
            ])
            ->defaultSort('date')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
