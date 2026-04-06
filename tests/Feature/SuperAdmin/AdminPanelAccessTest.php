<?php

use App\Enums\HomeDutyRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('allows super admin to access the admin panel', function () {
    $admin = User::factory()->createOne();
    $admin->assignRole(HomeDutyRole::SuperAdmin->value);

    actingAs($admin);

    get('/admin')
        ->assertOk();
});

it('denies regular users access to the admin panel', function () {
    $user = User::factory()->createOne();

    actingAs($user);

    get('/admin')
        ->assertForbidden();
});

it('redirects guests to the admin login page', function () {
    get('/admin')
        ->assertRedirect(route('filament.admin.auth.login'));
});
