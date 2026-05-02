<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\GroupMemberRole;
use App\Filament\Resources\Users\UserResource;
use App\Services\Groups\SyncUserGroupMembership;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $groupId = $this->data['group_id'] ?? null;
        $role = $this->data['group_role'] ?? null;

        if ($groupId === null) {
            return;
        }

        app(SyncUserGroupMembership::class)->handle(
            $this->record,
            (int) $groupId,
            $role !== null ? GroupMemberRole::from($role) : null,
        );
    }
}
