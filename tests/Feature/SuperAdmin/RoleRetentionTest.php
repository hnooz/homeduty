<?php

use App\Enums\GroupMemberRole;
use App\Enums\HomeDutyRole;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\GroupMember;
use App\Models\User;
use App\Services\Groups\CreateHomeGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

function createOwnedGroupForRetentionTest(User $owner, string $name = 'Maple House'): Group
{
    return app(CreateHomeGroup::class)->handle($owner, ['name' => $name]);
}

it('preserves super admin role when accepting a group invitation', function (): void {
    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = createOwnedGroupForRetentionTest($owner);

    /** @var User $invitee */
    $invitee = User::factory()->member()->createOne(['email' => 'super@example.com']);
    $invitee->assignRole(HomeDutyRole::SuperAdmin->value);

    $invitation = GroupInvitation::factory()->createOne([
        'group_id' => $group->id,
        'invited_by_user_id' => $owner->id,
        'email' => $invitee->email,
        'role' => GroupMemberRole::Member,
    ]);

    actingAs($invitee);

    post(route('group-invitations.accept', $invitation))
        ->assertRedirect(route('dashboard'));

    $fresh = $invitee->fresh();

    expect($fresh->hasRole(HomeDutyRole::SuperAdmin->value))->toBeTrue()
        ->and($fresh->hasRole(HomeDutyRole::GroupMember->value))->toBeTrue();
});

it('allows super admin to accept invitation while already in another group', function (): void {
    /** @var User $firstOwner */
    $firstOwner = User::factory()->createOne();
    $firstGroup = createOwnedGroupForRetentionTest($firstOwner, 'Maple House');

    /** @var User $secondOwner */
    $secondOwner = User::factory()->createOne();
    $secondGroup = createOwnedGroupForRetentionTest($secondOwner, 'Cedar Home');

    /** @var User $invitee */
    $invitee = User::factory()->member()->createOne(['email' => 'super-multi@example.com']);
    $invitee->assignRole(HomeDutyRole::SuperAdmin->value);

    GroupMember::query()->create([
        'group_id' => $firstGroup->id,
        'user_id' => $invitee->id,
        'role' => GroupMemberRole::Member,
    ]);

    $invitation = GroupInvitation::factory()->createOne([
        'group_id' => $secondGroup->id,
        'invited_by_user_id' => $secondOwner->id,
        'email' => $invitee->email,
        'role' => GroupMemberRole::Member,
    ]);

    actingAs($invitee);

    post(route('group-invitations.accept', $invitation))
        ->assertRedirect(route('dashboard'));

    expect(GroupMember::query()
        ->where('group_id', $secondGroup->id)
        ->where('user_id', $invitee->id)
        ->exists())->toBeTrue()
        ->and($invitee->fresh()->hasRole(HomeDutyRole::SuperAdmin->value))->toBeTrue();
});

it('allows super admin to join via link while already in another group', function (): void {
    /** @var User $firstOwner */
    $firstOwner = User::factory()->createOne();
    $firstGroup = createOwnedGroupForRetentionTest($firstOwner, 'Maple House');

    /** @var User $secondOwner */
    $secondOwner = User::factory()->createOne();
    $secondGroup = createOwnedGroupForRetentionTest($secondOwner, 'Cedar Home');

    /** @var User $superAdmin */
    $superAdmin = User::factory()->member()->createOne();
    $superAdmin->assignRole(HomeDutyRole::SuperAdmin->value);

    GroupMember::query()->create([
        'group_id' => $firstGroup->id,
        'user_id' => $superAdmin->id,
        'role' => GroupMemberRole::Member,
    ]);

    actingAs($superAdmin);

    get(route('groups.join', ['token' => $secondGroup->invite_token]))
        ->assertRedirect(route('groups.members.index', $secondGroup));

    expect(GroupMember::query()
        ->where('group_id', $secondGroup->id)
        ->where('user_id', $superAdmin->id)
        ->exists())->toBeTrue()
        ->and($superAdmin->fresh()->hasRole(HomeDutyRole::SuperAdmin->value))->toBeTrue();
});

it('preserves super admin role when a group member role is updated', function (): void {
    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = createOwnedGroupForRetentionTest($owner);

    /** @var User $member */
    $member = User::factory()->member()->createOne();
    $member->assignRole(HomeDutyRole::SuperAdmin->value);
    $member->assignRole(HomeDutyRole::GroupMember->value);

    /** @var GroupMember $membership */
    $membership = GroupMember::query()->create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'role' => GroupMemberRole::Member,
    ]);

    actingAs($owner);

    patch(route('groups.members.update', [$group, $membership]), [
        'role' => GroupMemberRole::Admin->value,
    ])->assertRedirect(route('groups.members.index', $group));

    $fresh = $member->fresh();

    expect($fresh->hasRole(HomeDutyRole::SuperAdmin->value))->toBeTrue()
        ->and($fresh->hasRole(HomeDutyRole::GroupAdmin->value))->toBeTrue()
        ->and($fresh->hasRole(HomeDutyRole::GroupMember->value))->toBeFalse();
});

it('preserves super admin role when removed from the last group membership', function (): void {
    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = createOwnedGroupForRetentionTest($owner);

    /** @var User $superAdmin */
    $superAdmin = User::factory()->member()->createOne();
    $superAdmin->assignRole(HomeDutyRole::SuperAdmin->value);
    $superAdmin->assignRole(HomeDutyRole::GroupMember->value);

    /** @var GroupMember $membership */
    $membership = GroupMember::query()->create([
        'group_id' => $group->id,
        'user_id' => $superAdmin->id,
        'role' => GroupMemberRole::Member,
    ]);

    actingAs($owner);

    delete(route('groups.members.destroy', [$group, $membership]))
        ->assertRedirect(route('groups.members.index', $group));

    $fresh = $superAdmin->fresh();

    expect($fresh->hasRole(HomeDutyRole::SuperAdmin->value))->toBeTrue()
        ->and($fresh->hasRole(HomeDutyRole::GroupMember->value))->toBeFalse()
        ->and($fresh->groupMemberships()->exists())->toBeFalse();
});
