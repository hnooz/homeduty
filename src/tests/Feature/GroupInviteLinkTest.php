<?php

use App\Enums\GroupMemberRole;
use App\Models\GroupMember;
use App\Models\User;
use App\Services\Groups\CreateHomeGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

it('generates an invite token automatically when a group is created', function (): void {
    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Maple House']);

    expect($group->invite_token)->not->toBeNull();
});

it('lets an authenticated user join a group via the invite link', function (): void {
    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Maple House']);

    /** @var User $joiner */
    $joiner = User::factory()->member()->createOne();

    actingAs($joiner);

    get(route('groups.join', ['token' => $group->invite_token]))
        ->assertRedirect(route('groups.members.index', $group));

    expect(GroupMember::query()
        ->where('group_id', $group->id)
        ->where('user_id', $joiner->id)
        ->where('role', GroupMemberRole::Member->value)
        ->exists())->toBeTrue();
});

it('regenerates the invite token for managers', function (): void {
    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Maple House']);
    $original = $group->invite_token;

    actingAs($owner);

    post(route('groups.invite-link.regenerate', $group))
        ->assertRedirect(route('groups.members.index', $group));

    expect($group->fresh()->invite_token)->not->toBe($original);
});
