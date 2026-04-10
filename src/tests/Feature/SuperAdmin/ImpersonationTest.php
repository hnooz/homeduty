<?php

use App\Enums\HomeDutyRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('forbids regular users from taking impersonation', function (): void {
    $actor = User::factory()->createOne();
    $target = User::factory()->createOne();

    actingAs($actor);

    get(route('impersonate', ['id' => $target->id]))
        ->assertForbidden();
});

it('allows super admins to impersonate non-admin users', function (): void {
    $admin = User::factory()->createOne();
    $admin->assignRole(HomeDutyRole::SuperAdmin->value);

    $target = User::factory()->createOne();

    actingAs($admin);

    get(route('impersonate', ['id' => $target->id]))
        ->assertRedirect();
});

it('forbids impersonating another super admin', function (): void {
    $admin = User::factory()->createOne();
    $admin->assignRole(HomeDutyRole::SuperAdmin->value);

    $other = User::factory()->createOne();
    $other->assignRole(HomeDutyRole::SuperAdmin->value);

    actingAs($admin);

    // Lab404 silently bails when canBeImpersonated() is false; assert no
    // impersonation session was started.
    get(route('impersonate', ['id' => $other->id]));

    expect(session()->has(config('laravel-impersonate.session_key')))->toBeFalse();
});
