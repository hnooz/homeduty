<?php

namespace Tests\Feature;

use App\Enums\GroupMemberRole;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(bool $isGroupAdmin = true): User
    {
        /** @var User $user */
        $user = User::factory()
            ->when(! $isGroupAdmin, fn ($factory): UserFactory => $factory->member())
            ->createOne();

        return $user;
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page): AssertableInertia => $page
                ->component('Dashboard')
                ->where('canCreateHomeGroup', true)
                ->where('canViewHomeGroupDuties', false)
                ->where('pendingInvitation', null)
            );
    }

    public function test_members_do_not_receive_admin_dashboard_capability(): void
    {
        $user = $this->makeUser(isGroupAdmin: false);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page): AssertableInertia => $page
                ->component('Dashboard')
                ->where('canCreateHomeGroup', false)
                ->where('canViewHomeGroupDuties', false)
                ->where('pendingInvitation', null)
            );
    }

    public function test_group_admins_with_an_existing_group_do_not_receive_create_capability(): void
    {
        $user = $this->makeUser();

        Group::factory()->create([
            'owner_id' => $user->id,
            'name' => 'Flat 4A',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page): AssertableInertia => $page
                ->component('Dashboard')
                ->where('canCreateHomeGroup', false)
                ->where('canViewHomeGroupDuties', true)
                ->where('canManageHomeGroupDuties', true)
                ->where('homeGroup.dutiesCount', 0)
                ->where('pendingInvitation', null)
                ->where('homeGroup.name', 'Flat 4A')
            );
    }

    public function test_dashboard_shows_a_pending_invitation_for_an_invited_user_without_a_group(): void
    {
        $owner = $this->makeUser();

        $group = Group::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'Harbor House',
        ]);

        $invitee = $this->makeUser(isGroupAdmin: false);

        GroupInvitation::query()->create([
            'group_id' => $group->id,
            'invited_by_user_id' => $owner->id,
            'name' => $invitee->name,
            'email' => $invitee->email,
            'phone_number' => $invitee->phone_number,
            'role' => GroupMemberRole::Member,
            'token' => 'dashboard-invitation-token',
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($invitee);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page): AssertableInertia => $page
                ->component('Dashboard')
                ->where('pendingInvitation.token', 'dashboard-invitation-token')
                ->where('pendingInvitation.groupName', 'Harbor House')
                ->where('pendingInvitation.roleLabel', 'Member')
                ->where('homeGroup', null)
            );
    }
}
