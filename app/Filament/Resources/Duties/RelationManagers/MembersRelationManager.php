<?php

namespace App\Filament\Resources\Duties\RelationManagers;

use App\Models\Duty;
use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    protected static ?string $title = 'Members (rotation)';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('sort_order')->numeric()->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->state(fn ($record) => $record->pivot->sort_order),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelect(function (Select $select): Select {
                        /** @var Duty $duty */
                        $duty = $this->getOwnerRecord();
                        $groupMemberIds = $duty->group->memberships()->pluck('user_id')->all();
                        $existing = $duty->members()->pluck('users.id')->all();
                        $eligible = array_values(array_diff($groupMemberIds, $existing));

                        return $select
                            ->label('Group member')
                            ->searchable()
                            ->preload()
                            ->options(User::query()
                                ->whereIn('id', $eligible)
                                ->pluck('name', 'id')
                                ->all());
                    })
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->required()
                            ->default(fn () => ($this->getOwnerRecord()->members()->max('sort_order') ?? 0) + 1),
                    ]),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
