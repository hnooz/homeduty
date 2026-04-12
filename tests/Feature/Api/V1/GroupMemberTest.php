<?php

use App\Enums\GroupMemberRole;
use App\Models\GroupMember;
use App\Models\User;
use App\Services\Groups\CreateHomeGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

it('lists members for a group', function (): void {
    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Oak Home']);

    Sanctum::actingAs($owner);

    getJson(route('api.v1.groups.members.index', $group))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.user_id', $owner->id);
});

it('updates a member role', function (): void {
    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Oak Home']);

    /** @var User $member */
    $member = User::factory()->member()->create();
    $membership = GroupMember::query()->create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'role' => GroupMemberRole::Member,
    ]);

    Sanctum::actingAs($owner);

    patchJson(route('api.v1.groups.members.update', [$group, $membership]), [
        'role' => GroupMemberRole::Admin->value,
    ])
        ->assertOk()
        ->assertJsonPath('data.role', 'admin');
});

it('prevents a non-admin from changing a member role', function (): void {
    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Oak Home']);

    /** @var User $member */
    $member = User::factory()->member()->create();
    $membership = GroupMember::query()->create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'role' => GroupMemberRole::Member,
    ]);

    Sanctum::actingAs($member);

    patchJson(route('api.v1.groups.members.update', [$group, $membership]), [
        'role' => GroupMemberRole::Admin->value,
    ])->assertForbidden();
});

it('removes a member from a group', function (): void {
    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Oak Home']);

    /** @var User $member */
    $member = User::factory()->member()->create();
    $membership = GroupMember::query()->create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'role' => GroupMemberRole::Member,
    ]);

    Sanctum::actingAs($owner);

    deleteJson(route('api.v1.groups.members.destroy', [$group, $membership]))
        ->assertOk();

    expect(GroupMember::query()->find($membership->id))->toBeNull();
});
