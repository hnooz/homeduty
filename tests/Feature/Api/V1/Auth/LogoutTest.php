<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('revokes the current token on logout', function (): void {
    /** @var User $user */
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    postJson(route('api.v1.auth.logout'))
        ->assertOk()
        ->assertJsonPath('message', 'Logged out successfully.');
});

it('rejects unauthenticated logout requests', function (): void {
    postJson(route('api.v1.auth.logout'))
        ->assertUnauthorized();
});
