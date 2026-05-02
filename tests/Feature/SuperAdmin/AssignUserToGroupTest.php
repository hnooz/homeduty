<?php

use App\Enums\GroupMemberRole;
use App\Enums\HomeDutyRole;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Services\Groups\SyncUserGroupMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->owner = User::factory()->createOne();
    $this->owner->assignRole(HomeDutyRole::GroupOwner->value);
    $this->group = Group::factory()->create(['owner_id' => $this->owner->id]);
});

it('assigns a user to a group with role', function () {
    $user = User::factory()->createOne();

    app(SyncUserGroupMembership::class)->handle($user, $this->group->id, GroupMemberRole::Admin);

    expect(GroupMember::where('group_id', $this->group->id)->where('user_id', $user->id)->exists())->toBeTrue();
    expect($user->fresh()->hasRole(HomeDutyRole::GroupAdmin->value))->toBeTrue();
});

it('moves a user from one group to another', function () {
    $other = Group::factory()->create(['owner_id' => User::factory()->create()->id]);
    $user = User::factory()->createOne();

    app(SyncUserGroupMembership::class)->handle($user, $other->id, GroupMemberRole::Member);
    app(SyncUserGroupMembership::class)->handle($user, $this->group->id, GroupMemberRole::Member);

    expect(GroupMember::where('user_id', $user->id)->count())->toBe(1);
    expect(GroupMember::where('group_id', $this->group->id)->where('user_id', $user->id)->exists())->toBeTrue();
});

it('removes a user from a group when group_id is null', function () {
    $user = User::factory()->createOne();
    app(SyncUserGroupMembership::class)->handle($user, $this->group->id, GroupMemberRole::Member);

    app(SyncUserGroupMembership::class)->handle($user, null, null);

    expect(GroupMember::where('user_id', $user->id)->exists())->toBeFalse();
});
