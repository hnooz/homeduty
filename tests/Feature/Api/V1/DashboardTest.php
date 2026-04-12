<?php

use App\Models\User;
use App\Services\Groups\CreateHomeGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

it('returns dashboard payload for a group owner', function (): void {
    /** @var User $owner */
    $owner = User::factory()->create();

    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Sunset Home']);

    Sanctum::actingAs($owner);

    getJson(route('api.v1.dashboard'))
        ->assertOk()
        ->assertJsonPath('data.home_group.id', $group->id)
        ->assertJsonPath('data.home_group.name', 'Sunset Home')
        ->assertJsonPath('data.abilities.can_manage_members', true);
});

it('returns a null group for a brand new user', function (): void {
    /** @var User $user */
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    getJson(route('api.v1.dashboard'))
        ->assertOk()
        ->assertJsonPath('data.home_group', null);
});

it('rejects unauthenticated dashboard requests', function (): void {
    getJson(route('api.v1.dashboard'))->assertUnauthorized();
});
