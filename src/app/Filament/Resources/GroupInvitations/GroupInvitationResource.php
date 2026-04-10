<?php

namespace App\Filament\Resources\GroupInvitations;

use App\Filament\Resources\GroupInvitations\Pages\CreateGroupInvitation;
use App\Filament\Resources\GroupInvitations\Pages\EditGroupInvitation;
use App\Filament\Resources\GroupInvitations\Pages\ListGroupInvitations;
use App\Filament\Resources\GroupInvitations\Schemas\GroupInvitationForm;
use App\Filament\Resources\GroupInvitations\Tables\GroupInvitationsTable;
use App\Models\GroupInvitation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GroupInvitationResource extends Resource
{
    protected static ?string $model = GroupInvitation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static \UnitEnum|string|null $navigationGroup = 'Group Management';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return GroupInvitationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GroupInvitationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGroupInvitations::route('/'),
            'create' => CreateGroupInvitation::route('/create'),
            'edit' => EditGroupInvitation::route('/{record}/edit'),
        ];
    }
}
