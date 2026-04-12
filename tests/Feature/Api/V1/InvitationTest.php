<?php

use App\Enums\GroupMemberRole;
use App\Models\GroupInvitation;
use App\Models\GroupMember;
use App\Models\User;
use App\Services\Groups\CreateHomeGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Notification::fake();
});

it('creates an invitation for a group', function (): void {
    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Aspen Home']);

    Sanctum::actingAs($owner);

    postJson(route('api.v1.groups.invitations.store', $group), [
        'name' => 'Alan Turing',
        'email' => 'alan@example.com',
        'role' => GroupMemberRole::Member->value,
    ])
        ->assertCreated()
        ->assertJsonPath('data.email', 'alan@example.com');

    expect(GroupInvitation::query()->where('email', 'alan@example.com')->exists())->toBeTrue();
});

it('cancels a pending invitation', function (): void {
    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Aspen Home']);

    $invitation = GroupInvitation::factory()->create([
        'group_id' => $group->id,
        'invited_by_user_id' => $owner->id,
    ]);

    Sanctum::actingAs($owner);

    deleteJson(route('api.v1.groups.invitations.destroy', [$group, $invitation]))
        ->assertOk();

    expect(GroupInvitation::query()->find($invitation->id))->toBeNull();
});

it('lists pending invitations for the current user', function (): void {
    /** @var User $user */
    $user = User::factory()->member()->create(['email' => 'invitee@example.com']);

    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Aspen Home']);

    GroupInvitation::factory()->create([
        'group_id' => $group->id,
        'invited_by_user_id' => $owner->id,
        'email' => 'invitee@example.com',
    ]);

    Sanctum::actingAs($user);

    getJson(route('api.v1.invitations.index'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.email', 'invitee@example.com');
});

it('shows an invitation addressed to the current user', function (): void {
    /** @var User $user */
    $user = User::factory()->member()->create(['email' => 'invitee@example.com']);

    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Aspen Home']);

    $invitation = GroupInvitation::factory()->create([
        'group_id' => $group->id,
        'invited_by_user_id' => $owner->id,
        'email' => 'invitee@example.com',
    ]);

    Sanctum::actingAs($user);

    getJson(route('api.v1.invitations.show', $invitation))
        ->assertOk()
        ->assertJsonPath('data.token', $invitation->token);
});

it('denies viewing an invitation addressed to someone else', function (): void {
    /** @var User $user */
    $user = User::factory()->member()->create(['email' => 'someone-else@example.com']);

    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Aspen Home']);

    $invitation = GroupInvitation::factory()->create([
        'group_id' => $group->id,
        'invited_by_user_id' => $owner->id,
        'email' => 'invitee@example.com',
    ]);

    Sanctum::actingAs($user);

    getJson(route('api.v1.invitations.show', $invitation))->assertForbidden();
});

it('accepts an invitation', function (): void {
    /** @var User $user */
    $user = User::factory()->member()->create(['email' => 'invitee@example.com']);

    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Aspen Home']);

    $invitation = GroupInvitation::factory()->create([
        'group_id' => $group->id,
        'invited_by_user_id' => $owner->id,
        'email' => 'invitee@example.com',
        'role' => GroupMemberRole::Member,
    ]);

    Sanctum::actingAs($user);

    postJson(route('api.v1.invitations.accept', $invitation))
        ->assertOk();

    expect(GroupMember::query()
        ->where('group_id', $group->id)
        ->where('user_id', $user->id)
        ->exists())->toBeTrue();
});

it('declines an invitation', function (): void {
    /** @var User $user */
    $user = User::factory()->member()->create(['email' => 'invitee@example.com']);

    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Aspen Home']);

    $invitation = GroupInvitation::factory()->create([
        'group_id' => $group->id,
        'invited_by_user_id' => $owner->id,
        'email' => 'invitee@example.com',
    ]);

    Sanctum::actingAs($user);

    postJson(route('api.v1.invitations.decline', $invitation))
        ->assertOk();

    expect(GroupInvitation::query()->find($invitation->id))->toBeNull();
});
