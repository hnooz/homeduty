<?php

namespace App\Filament\Resources\Groups\RelationManagers;

use App\Enums\GroupMemberRole;
use App\Enums\HomeDutyRole;
use App\Models\GroupMember;
use App\Models\User;
use App\Services\Groups\RemoveGroupMember;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'memberships';

    protected static ?string $title = 'Members';

    protected static ?string $recordTitleAttribute = 'user.name';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('User')
                ->required()
                ->searchable()
                ->preload()
                ->getSearchResultsUsing(function (string $search): array {
                    $existing = $this->getOwnerRecord()->memberships()->pluck('user_id')->all();

                    return User::query()
                        ->whereNotIn('id', $existing)
                        ->where(function ($q) use ($search): void {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->limit(25)
                        ->pluck('name', 'id')
                        ->all();
                })
                ->getOptionLabelUsing(fn ($value): ?string => User::find($value)?->name)
                ->disabledOn('edit'),
            Select::make('role')
                ->options(GroupMemberRole::class)
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                TextColumn::make('user.name')->label('Name')->searchable(),
                TextColumn::make('user.email')->label('Email')->searchable(),
                TextColumn::make('role')->badge(),
                TextColumn::make('created_at')->label('Joined')->dateTime()->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->using(function (array $data): Model {
                        return DB::transaction(function () use ($data): GroupMember {
                            $group = $this->getOwnerRecord();
                            $role = $data['role'] instanceof GroupMemberRole
                                ? $data['role']
                                : GroupMemberRole::from($data['role']);

                            $membership = $group->memberships()->create([
                                'user_id' => $data['user_id'],
                                'role' => $role,
                            ]);

                            $user = $membership->user;
                            $newRole = $role->toHomeDutyRole();

                            foreach ([HomeDutyRole::GroupAdmin, HomeDutyRole::GroupMember] as $groupRole) {
                                if ($groupRole !== $newRole && $user->hasRole($groupRole->value)) {
                                    $user->removeRole($groupRole->value);
                                }
                            }

                            $user->assignRole($newRole->value);

                            $user->forceFill([
                                'is_group_admin' => $user->hasRole(HomeDutyRole::SuperAdmin->value)
                                    || $newRole === HomeDutyRole::GroupAdmin,
                            ])->saveQuietly();

                            return $membership;
                        });
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->using(function (Model $record, array $data): Model {
                        return DB::transaction(function () use ($record, $data): Model {
                            $role = $data['role'] instanceof GroupMemberRole
                                ? $data['role']
                                : GroupMemberRole::from($data['role']);

                            $record->update(['role' => $role]);

                            $user = $record->user;
                            $newRole = $role->toHomeDutyRole();

                            foreach ([HomeDutyRole::GroupAdmin, HomeDutyRole::GroupMember] as $groupRole) {
                                if ($groupRole !== $newRole && $user->hasRole($groupRole->value)) {
                                    $user->removeRole($groupRole->value);
                                }
                            }

                            $user->assignRole($newRole->value);

                            $user->forceFill([
                                'is_group_admin' => $user->hasRole(HomeDutyRole::SuperAdmin->value)
                                    || $newRole === HomeDutyRole::GroupAdmin,
                            ])->saveQuietly();

                            return $record;
                        });
                    }),
                Action::make('remove')
                    ->label('Remove')
                    ->icon('heroicon-o-user-minus')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (GroupMember $record): void {
                        app(RemoveGroupMember::class)->handle($record);
                    }),
                DeleteAction::make()
                    ->visible(false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records): void {
                            $service = app(RemoveGroupMember::class);
                            foreach ($records as $record) {
                                $service->handle($record);
                            }
                        }),
                ]),
            ]);
    }
}
