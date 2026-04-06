<?php

use App\Enums\HomeDutyPermission;
use App\Enums\HomeDutyRole;
use App\Services\Roles\SyncHomeDutyAuthorization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('creates the super admin role with all permissions', function () {
    (new SyncHomeDutyAuthorization)->handle();

    $superAdmin = Role::findByName(HomeDutyRole::SuperAdmin->value, 'web');

    expect($superAdmin)->not->toBeNull();
    expect($superAdmin->hasPermissionTo(HomeDutyPermission::CreateHomeGroup->value))->toBeTrue();
    expect($superAdmin->hasPermissionTo(HomeDutyPermission::ManageHomeGroupMembers->value))->toBeTrue();
    expect($superAdmin->hasPermissionTo(HomeDutyPermission::ManageHomeGroupDuties->value))->toBeTrue();
    expect($superAdmin->hasPermissionTo(HomeDutyPermission::AccessAdminPanel->value))->toBeTrue();
});

it('creates the access admin panel permission', function () {
    (new SyncHomeDutyAuthorization)->handle();

    expect(
        Permission::findByName(HomeDutyPermission::AccessAdminPanel->value, 'web')
    )->not->toBeNull();
});
