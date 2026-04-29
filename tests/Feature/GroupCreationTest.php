<?php

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

it('allows a group admin without a group to view the home group creation page', function () {
    /** @var User $admin */
    $admin = User::factory()->createOne();

    actingAs($admin);

    get(route('groups.create'))
        ->assertOk();
});

it('prevents non-admin members from viewing the home group creation page', function () {
    /** @var User $member */
    $member = User::factory()->member()->createOne();

    actingAs($member);

    get(route('groups.create'))
        ->assertForbidden();
});

it('creates a home group and adds the owner as the first admin member', function () {
    /** @var User $admin */
    $admin = User::factory()->createOne();

    actingAs($admin);

    post(route('groups.store'), [
        'name' => 'Sunset Apartment',
    ])
        ->assertRedirect(route('dashboard'));

    $group = Group::query()->where('name', 'Sunset Apartment')->firstOrFail();

    expect($group->owner_id)->toBe($admin->id);

    expect(GroupMember::query()
        ->where('group_id', $group->id)
        ->where('user_id', $admin->id)
        ->where('role', 'admin')
        ->exists())->toBeTrue();
});

it('does not allow a group admin to create a second home group', function () {
    /** @var User $admin */
    $admin = User::factory()->createOne();

    Group::factory()->create([
        'owner_id' => $admin->id,
        'name' => 'First Home',
    ]);

    actingAs($admin);

    post(route('groups.store'), [
        'name' => 'Second Home',
    ])
        ->assertForbidden();

    expect(Group::query()->where('name', 'Second Home')->exists())->toBeFalse();
});
