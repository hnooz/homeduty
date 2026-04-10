<?php

use App\Enums\HomeDutyRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

it('promotes a user to super admin by email', function () {
    $user = User::factory()->createOne();

    artisan('homeduty:promote-admin', ['email' => $user->email])
        ->expectsOutput("User [{$user->email}] has been promoted to Super Admin.")
        ->assertSuccessful();

    expect($user->fresh()->hasRole(HomeDutyRole::SuperAdmin->value))->toBeTrue();
});

it('warns if user is already a super admin', function () {
    $user = User::factory()->createOne();
    $user->assignRole(HomeDutyRole::SuperAdmin->value);

    artisan('homeduty:promote-admin', ['email' => $user->email])
        ->expectsOutput("User [{$user->email}] is already a Super Admin.")
        ->assertSuccessful();
});

it('fails if user does not exist', function () {
    artisan('homeduty:promote-admin', ['email' => 'nobody@example.com'])
        ->expectsOutput('User with email [nobody@example.com] not found.')
        ->assertFailed();
});
