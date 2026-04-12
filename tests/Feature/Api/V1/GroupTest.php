<?php

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Services\Groups\CreateHomeGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('lists groups the user belongs to', function (): void {
    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Riverside Home']);

    Sanctum::actingAs($owner);

    getJson(route('api.v1.groups.index'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $group->id);
});

it('creates a new home group', function (): void {
    /** @var User $admin */
    $admin = User::factory()->create();

    Sanctum::actingAs($admin);

    postJson(route('api.v1.groups.store'), [
        'name' => 'Lakeside Home',
    ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Lakeside Home')
        ->assertJsonPath('data.is_owner', true);

    expect(Group::query()->where('name', 'Lakeside Home')->exists())->toBeTrue();
});

it('shows a single group', function (): void {
    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Cedar Home']);

    Sanctum::actingAs($owner);

    getJson(route('api.v1.groups.show', $group))
        ->assertOk()
        ->assertJsonPath('data.id', $group->id);
});

it('denies showing a group the user does not belong to', function (): void {
    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Cedar Home']);

    /** @var User $outsider */
    $outsider = User::factory()->create();

    Sanctum::actingAs($outsider);

    getJson(route('api.v1.groups.show', $group))->assertForbidden();
});

it('allows the owner to update a group', function (): void {
    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Cedar Home']);

    Sanctum::actingAs($owner);

    patchJson(route('api.v1.groups.update', $group), ['name' => 'Cedar Estate'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Cedar Estate');
});

it('forbids a non-owner from updating a group', function (): void {
    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Cedar Home']);

    /** @var User $member */
    $member = User::factory()->member()->create();
    GroupMember::query()->create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'role' => 'member',
    ]);

    Sanctum::actingAs($member);

    patchJson(route('api.v1.groups.update', $group), ['name' => 'Hijacked'])
        ->assertForbidden();
});

it('allows the owner to delete a group', function (): void {
    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Cedar Home']);

    Sanctum::actingAs($owner);

    deleteJson(route('api.v1.groups.destroy', $group))
        ->assertOk();

    expect(Group::query()->withTrashed()->find($group->id)->deleted_at)->not->toBeNull();
});
