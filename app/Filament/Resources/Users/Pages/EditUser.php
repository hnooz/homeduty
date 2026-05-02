<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\GroupMemberRole;
use App\Filament\Resources\Users\UserResource;
use App\Services\Groups\SyncUserGroupMembership;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $groupId = $this->data['group_id'] ?? null;
        $role = $this->data['group_role'] ?? null;

        app(SyncUserGroupMembership::class)->handle(
            $this->record,
            $groupId !== null ? (int) $groupId : null,
            $role !== null ? GroupMemberRole::from($role) : null,
        );
    }
}
