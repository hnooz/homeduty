<?php

use App\Enums\GroupMemberRole;
use App\Enums\HomeDutyRole;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\GroupMember;
use App\Models\User;
use App\Notifications\HomeGroupInvitationNotification;
use App\Services\Groups\CreateHomeGroup;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

function createOwnedGroupFor(User $owner, string $name = 'Maple House'): Group
{
    return app(CreateHomeGroup::class)->handle($owner, [
        'name' => $name,
    ]);
}

it('allows a home group owner to invite a new member', function (): void {
    Notification::fake();

    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = createOwnedGroupFor($owner);

    actingAs($owner);

    post(route('groups.invitations.store', $group), [
        'name' => 'Alex Rivera',
        'email' => 'alex@example.com',
        'phone_number' => '+15550001111',
        'role' => GroupMemberRole::Member->value,
    ])->assertRedirect(route('groups.members.index', $group));

    $invitation = GroupInvitation::query()->where('email', 'alex@example.com')->firstOrFail();

    expect($invitation->group_id)->toBe($group->id)
        ->and($invitation->role)->toBe(GroupMemberRole::Member);

    Notification::assertSentOnDemand(HomeGroupInvitationNotification::class, function (HomeGroupInvitationNotification $notification, array $channels, object $notifiable) use ($invitation): bool {
        return in_array('mail', $channels, true)
            && $notifiable->routes['mail'] === $invitation->email;
    });
});

it('allows an invited existing user to accept a pending invitation', function (): void {
    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = createOwnedGroupFor($owner);

    /** @var User $invitee */
    $invitee = User::factory()->member()->createOne([
        'email' => 'sam@example.com',
    ]);

    $invitation = GroupInvitation::factory()->createOne([
        'group_id' => $group->id,
        'invited_by_user_id' => $owner->id,
        'email' => $invitee->email,
        'role' => GroupMemberRole::Admin,
    ]);

    actingAs($invitee);

    post(route('group-invitations.accept', $invitation))
        ->assertRedirect(route('dashboard'));

    expect(GroupMember::query()
        ->where('group_id', $group->id)
        ->where('user_id', $invitee->id)
        ->where('role', GroupMemberRole::Admin->value)
        ->exists())->toBeTrue();

    expect($invitee->fresh()->hasRole(HomeDutyRole::GroupAdmin))->toBeTrue()
        ->and($invitation->fresh()->accepted_by_user_id)->toBe($invitee->id);
});

it('auto-accepts a matching invitation during registration', function (): void {
    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = createOwnedGroupFor($owner);

    $invitation = GroupInvitation::factory()->createOne([
        'group_id' => $group->id,
        'invited_by_user_id' => $owner->id,
        'email' => 'newmember@example.com',
        'role' => GroupMemberRole::Member,
    ]);

    post(route('register.store'), [
        'name' => 'New Member',
        'email' => 'newmember@example.com',
        'phone_number' => '+15554443333',
        'password' => 'password',
        'password_confirmation' => 'password',
        'invitation_token' => $invitation->token,
    ])->assertRedirect(route('dashboard', absolute: false));

    $member = User::query()->where('email', 'newmember@example.com')->firstOrFail();

    expect($member->hasRole(HomeDutyRole::GroupMember))->toBeTrue()
        ->and($member->hasRole(HomeDutyRole::GroupOwner))->toBeFalse()
        ->and($member->is_group_admin)->toBeFalse();

    expect(GroupMember::query()
        ->where('group_id', $group->id)
        ->where('user_id', $member->id)
        ->where('role', GroupMemberRole::Member->value)
        ->exists())->toBeTrue();
});

it('prevents non-owners from inviting additional members', function (): void {
    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = createOwnedGroupFor($owner);

    /** @var User $member */
    $member = User::factory()->member()->createOne();

    GroupMember::query()->create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'role' => GroupMemberRole::Member,
    ]);

    actingAs($member);

    post(route('groups.invitations.store', $group), [
        'name' => 'Blocked Invite',
        'email' => 'blocked@example.com',
        'role' => GroupMemberRole::Member->value,
    ])->assertForbidden();
});

it('allows an invited admin to manage members after accepting the invitation', function (): void {
    Notification::fake();

    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = createOwnedGroupFor($owner);

    /** @var User $admin */
    $admin = User::factory()->member()->createOne([
        'email' => 'admin@example.com',
    ]);

    $invitation = GroupInvitation::factory()->createOne([
        'group_id' => $group->id,
        'invited_by_user_id' => $owner->id,
        'email' => $admin->email,
        'role' => GroupMemberRole::Admin,
    ]);

    actingAs($admin);

    post(route('group-invitations.accept', $invitation))
        ->assertRedirect(route('dashboard'));

    post(route('groups.invitations.store', $group), [
        'name' => 'Jordan Tate',
        'email' => 'jordan@example.com',
        'role' => GroupMemberRole::Member->value,
    ])->assertRedirect(route('groups.members.index', $group));

    $member = User::factory()->member()->createOne();

    /** @var GroupMember $membership */
    $membership = GroupMember::query()->create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'role' => GroupMemberRole::Member,
    ]);

    patch(route('groups.members.update', [$group, $membership]), [
        'role' => GroupMemberRole::Admin->value,
    ])->assertRedirect(route('groups.members.index', $group));

    expect($membership->fresh()->role)->toBe(GroupMemberRole::Admin);

    delete(route('groups.members.destroy', [$group, $membership]))
        ->assertRedirect(route('groups.members.index', $group));

    expect(GroupMember::query()->whereKey($membership->id)->exists())->toBeFalse();
    expect(GroupInvitation::query()->where('email', 'jordan@example.com')->exists())->toBeTrue();
});

it('allows a group admin to directly accept a registered pending member invitation', function (): void {
    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = createOwnedGroupFor($owner);

    /** @var User $admin */
    $admin = User::factory()->member()->createOne([
        'email' => 'admin-direct@example.com',
    ]);

    $adminInvitation = GroupInvitation::factory()->createOne([
        'group_id' => $group->id,
        'invited_by_user_id' => $owner->id,
        'email' => $admin->email,
        'role' => GroupMemberRole::Admin,
    ]);

    actingAs($admin);

    post(route('group-invitations.accept', $adminInvitation))
        ->assertRedirect(route('dashboard'));

    /** @var User $invitee */
    $invitee = User::factory()->member()->createOne([
        'email' => 'member-direct@example.com',
    ]);

    $memberInvitation = GroupInvitation::factory()->createOne([
        'group_id' => $group->id,
        'invited_by_user_id' => $owner->id,
        'email' => $invitee->email,
        'role' => GroupMemberRole::Member,
    ]);

    post(route('groups.invitations.accept-direct', [$group, $memberInvitation]))
        ->assertRedirect(route('groups.members.index', $group));

    expect(GroupMember::query()
        ->where('group_id', $group->id)
        ->where('user_id', $invitee->id)
        ->where('role', GroupMemberRole::Member->value)
        ->exists())->toBeTrue();

    expect($memberInvitation->fresh()->accepted_by_user_id)->toBe($invitee->id)
        ->and($invitee->fresh()->hasRole(HomeDutyRole::GroupMember))->toBeTrue();
});

it('creates an account and sends a password reset link when directly accepting an unregistered invitation', function (): void {
    Notification::fake();

    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = createOwnedGroupFor($owner);

    /** @var User $admin */
    $admin = User::factory()->member()->createOne([
        'email' => 'admin-creates@example.com',
    ]);

    $adminInvitation = GroupInvitation::factory()->createOne([
        'group_id' => $group->id,
        'invited_by_user_id' => $owner->id,
        'email' => $admin->email,
        'role' => GroupMemberRole::Admin,
    ]);

    actingAs($admin);

    post(route('group-invitations.accept', $adminInvitation))
        ->assertRedirect(route('dashboard'));

    $memberInvitation = GroupInvitation::factory()->createOne([
        'group_id' => $group->id,
        'invited_by_user_id' => $owner->id,
        'email' => 'brand-new-member@example.com',
        'name' => 'Brand New Member',
        'phone_number' => '+15559998888',
        'role' => GroupMemberRole::Member,
    ]);

    post(route('groups.invitations.accept-direct', [$group, $memberInvitation]))
        ->assertRedirect(route('groups.members.index', $group));

    $createdUser = User::query()->where('email', 'brand-new-member@example.com')->firstOrFail();

    expect($createdUser->name)->toBe('Brand New Member')
        ->and($createdUser->phone_number)->toBe('+15559998888')
        ->and($createdUser->hasRole(HomeDutyRole::GroupMember))->toBeTrue();

    expect(GroupMember::query()
        ->where('group_id', $group->id)
        ->where('user_id', $createdUser->id)
        ->where('role', GroupMemberRole::Member->value)
        ->exists())->toBeTrue();

    expect($memberInvitation->fresh()->accepted_by_user_id)->toBe($createdUser->id);

    Notification::assertSentTo($createdUser, ResetPassword::class);
});

it('exposes pending invitation registration state to group managers', function (): void {
    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = createOwnedGroupFor($owner);

    /** @var User $admin */
    $admin = User::factory()->member()->createOne([
        'email' => 'members-admin@example.com',
    ]);

    $adminInvitation = GroupInvitation::factory()->createOne([
        'group_id' => $group->id,
        'invited_by_user_id' => $owner->id,
        'email' => $admin->email,
        'role' => GroupMemberRole::Admin,
    ]);

    actingAs($admin);

    post(route('group-invitations.accept', $adminInvitation))
        ->assertRedirect(route('dashboard'));

    $registeredInvitee = User::factory()->member()->createOne([
        'email' => 'registered-pending@example.com',
        'name' => 'Registered Pending',
    ]);

    GroupInvitation::factory()->createOne([
        'group_id' => $group->id,
        'invited_by_user_id' => $owner->id,
        'email' => 'waiting-on-signin@example.com',
        'name' => 'Waiting Invitee',
        'role' => GroupMemberRole::Member,
    ]);

    GroupInvitation::factory()->createOne([
        'group_id' => $group->id,
        'invited_by_user_id' => $owner->id,
        'email' => $registeredInvitee->email,
        'name' => $registeredInvitee->name,
        'role' => GroupMemberRole::Member,
    ]);

    get(route('groups.members.index', $group))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('groups/Members')
            ->where('canManageMembers', true)
            ->has('pendingInvitations', 2)
            ->where('pendingInvitations.0.email', $registeredInvitee->email)
            ->where('pendingInvitations.0.hasRegisteredUser', true)
            ->where('pendingInvitations.0.registeredUserName', $registeredInvitee->name)
            ->where('pendingInvitations.1.email', 'waiting-on-signin@example.com')
            ->where('pendingInvitations.1.hasRegisteredUser', false)
            ->where('pendingInvitations.1.registeredUserName', null)
        );
});

it('allows an owner to update and remove a group member', function (): void {
    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = createOwnedGroupFor($owner);

    /** @var User $member */
    $member = User::factory()->member()->createOne();

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

    expect($membership->fresh()->role)->toBe(GroupMemberRole::Admin)
        ->and($member->fresh()->hasRole(HomeDutyRole::GroupAdmin))->toBeTrue();

    delete(route('groups.members.destroy', [$group, $membership]))
        ->assertRedirect(route('groups.members.index', $group));

    expect(GroupMember::query()->whereKey($membership->id)->exists())->toBeFalse();
});
