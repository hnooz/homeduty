<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(bool $isGroupAdmin = true): User
    {
        /** @var User $user */
        $user = User::factory()
            ->when(! $isGroupAdmin, fn ($factory) => $factory->member())
            ->createOne();

        return $user;
    }

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('canCreateHomeGroup', true)
            );
    }

    public function test_members_do_not_receive_admin_dashboard_capability()
    {
        $user = $this->makeUser(isGroupAdmin: false);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('canCreateHomeGroup', false)
            );
    }

    public function test_group_admins_with_an_existing_group_do_not_receive_create_capability()
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
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('canCreateHomeGroup', false)
                ->where('homeGroup.name', 'Flat 4A')
            );
    }
}
