<?php

namespace App\Services\Roles;

use App\Enums\HomeDutyPermission;
use App\Enums\HomeDutyRole;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SyncHomeDutyAuthorization
{
    public function handle(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $createHomeGroup = Permission::findOrCreate(HomeDutyPermission::CreateHomeGroup->value, 'web');
        $manageHomeGroupMembers = Permission::findOrCreate(HomeDutyPermission::ManageHomeGroupMembers->value, 'web');
        $manageHomeGroupDuties = Permission::findOrCreate(HomeDutyPermission::ManageHomeGroupDuties->value, 'web');
        $accessAdminPanel = Permission::findOrCreate(HomeDutyPermission::AccessAdminPanel->value, 'web');

        $superAdmin = Role::findOrCreate(HomeDutyRole::SuperAdmin->value, 'web');
        $groupOwner = Role::findOrCreate(HomeDutyRole::GroupOwner->value, 'web');
        $groupAdmin = Role::findOrCreate(HomeDutyRole::GroupAdmin->value, 'web');
        $groupMember = Role::findOrCreate(HomeDutyRole::GroupMember->value, 'web');

        $superAdmin->syncPermissions([
            $createHomeGroup,
            $manageHomeGroupMembers,
            $manageHomeGroupDuties,
            $accessAdminPanel,
        ]);

        $groupOwner->syncPermissions([
            $createHomeGroup,
            $manageHomeGroupMembers,
            $manageHomeGroupDuties,
        ]);

        $groupAdmin->syncPermissions([
            $manageHomeGroupMembers,
            $manageHomeGroupDuties,
        ]);

        $groupMember->syncPermissions([]);
    }
}
