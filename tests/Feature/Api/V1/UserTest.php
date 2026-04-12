<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

it('returns the authenticated user profile', function (): void {
    /** @var User $user */
    $user = User::factory()->create(['name' => 'Grace Hopper']);

    Sanctum::actingAs($user);

    getJson(route('api.v1.user.show'))
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.name', 'Grace Hopper');
});

it('updates the authenticated user profile', function (): void {
    /** @var User $user */
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    patchJson(route('api.v1.user.update'), [
        'name' => 'New Name',
        'email' => 'new-email@example.com',
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'New Name')
        ->assertJsonPath('data.email', 'new-email@example.com');

    expect($user->fresh()->email)->toBe('new-email@example.com');
});

it('rejects unauthenticated user profile requests', function (): void {
    getJson(route('api.v1.user.show'))->assertUnauthorized();
});
