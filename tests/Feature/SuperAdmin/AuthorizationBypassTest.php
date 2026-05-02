<?php

use App\Enums\HomeDutyPermission;
use App\Enums\HomeDutyRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('allows super admins to pass ability checks without explicit permissions', function (): void {
    /** @var User $superAdmin */
    $superAdmin = User::factory()->createOne();
    $superAdmin->assignRole(HomeDutyRole::SuperAdmin->value);

    /** @var User $member */
    $member = User::factory()->member()->createOne();

    Role::findOrCreate(HomeDutyRole::SuperAdmin->value, 'web')->syncPermissions([]);

    expect($superAdmin->can(HomeDutyPermission::CreateHomeGroup->value))->toBeTrue()
        ->and($member->can(HomeDutyPermission::CreateHomeGroup->value))->toBeFalse();
});

it('allows super admins to pass arbitrary ability checks', function (): void {
    /** @var User $superAdmin */
    $superAdmin = User::factory()->createOne();
    $superAdmin->assignRole(HomeDutyRole::SuperAdmin->value);

    /** @var User $member */
    $member = User::factory()->createOne();

    expect($superAdmin->can('system.anything'))->toBeTrue()
        ->and($member->can('system.anything'))->toBeFalse();
});
