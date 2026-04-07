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
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

function createOwnedGroupForRetentionTest(User $owner, string $name = 'Maple House'): Group
{
    return app(CreateHomeGroup::class)->handle($owner, ['name' => $name]);
}

it('preserves super admin role when accepting a group invitation', function (): void {
    $owner = User::factory()->createOne();
    $group = createOwnedGroupForRetentionTest($owner);

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

it('preserves super admin role when a group member role is updated', function (): void {
    $owner = User::factory()->createOne();
    $group = createOwnedGroupForRetentionTest($owner);

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
