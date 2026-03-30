<?php

use App\Enums\DutyType;
use App\Enums\HomeDutyRole;
use App\Models\Duty;
use App\Models\DutySlot;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Services\Groups\CreateHomeGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

function createGroupForDutyTests(User $owner, string $name = 'Harbor House'): Group
{
    return app(CreateHomeGroup::class)->handle($owner, [
        'name' => $name,
    ]);
}

it('shows the duties page to group members', function (): void {
    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = createGroupForDutyTests($owner);

    /** @var User $member */
    $member = User::factory()->member()->createOne();

    GroupMember::query()->create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'role' => 'member',
    ]);

    actingAs($member);

    get(route('groups.duties.index', $group))
        ->assertOk()
        ->assertInertia(fn (Assert $page): AssertableInertia => $page
            ->component('groups/Duties')
            ->where('group.name', $group->name)
            ->where('canManageDuties', false)
            ->has('duties', 0)
        );
});

it('allows a home group owner to create a duty with members', function (): void {
    Notification::fake();

    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = createGroupForDutyTests($owner);

    /** @var User $member */
    $member = User::factory()->member()->createOne();

    GroupMember::query()->create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'role' => 'member',
    ]);

    actingAs($owner);

    post(route('groups.duties.store', $group), [
        'type' => DutyType::Cooking->value,
        'starts_on' => now()->toDateString(),
        'member_ids' => [$owner->id, $member->id],
    ])->assertRedirect(route('groups.duties.index', $group));

    $duty = Duty::query()->where('type', DutyType::Cooking)->firstOrFail();

    expect($duty->group_id)->toBe($group->id)
        ->and($duty->type)->toBe(DutyType::Cooking)
        ->and($duty->members)->toHaveCount(2);

    expect(DutySlot::query()->where('duty_id', $duty->id)->count())->toBeGreaterThan(0);
});

it('prevents regular members from creating duties', function (): void {
    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = createGroupForDutyTests($owner);

    /** @var User $member */
    $member = User::factory()->member()->createOne();

    GroupMember::query()->create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'role' => 'member',
    ]);

    actingAs($member);

    post(route('groups.duties.store', $group), [
        'type' => DutyType::Cleaning->value,
        'starts_on' => now()->toDateString(),
        'member_ids' => [$member->id],
    ])->assertForbidden();

    expect(Duty::query()->where('type', DutyType::Cleaning)->exists())->toBeFalse();
});

it('rejects member_ids containing users not in the home group', function (): void {
    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = createGroupForDutyTests($owner);

    /** @var User $outsider */
    $outsider = User::factory()->member()->createOne();

    actingAs($owner);

    post(route('groups.duties.store', $group), [
        'type' => DutyType::Cooking->value,
        'starts_on' => now()->toDateString(),
        'member_ids' => [$outsider->id],
    ])->assertSessionHasErrors('member_ids.0');

    expect(Duty::query()->where('type', DutyType::Cooking)->exists())->toBeFalse();
});

it('allows an admin to update and remove a planned duty', function (): void {
    Notification::fake();

    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = createGroupForDutyTests($owner);

    /** @var User $admin */
    $admin = User::factory()->member()->createOne();
    /** @var User $member */
    $member = User::factory()->member()->createOne();

    GroupMember::query()->create([
        'group_id' => $group->id,
        'user_id' => $admin->id,
        'role' => 'admin',
    ]);

    $admin->syncRoles(HomeDutyRole::GroupAdmin);
    $admin->forceFill([
        'is_group_admin' => true,
    ])->saveQuietly();

    GroupMember::query()->create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'role' => 'member',
    ]);

    /** @var Duty $duty */
    $duty = Duty::query()->create([
        'group_id' => $group->id,
        'type' => DutyType::Cooking,
        'starts_on' => now()->toDateString(),
    ]);

    $duty->members()->attach($owner->id, ['sort_order' => 0]);

    actingAs($admin);

    patch(route('groups.duties.update', [$group, $duty]), [
        'type' => DutyType::Cleaning->value,
        'starts_on' => now()->addDay()->toDateString(),
        'member_ids' => [$admin->id, $member->id],
    ])->assertRedirect(route('groups.duties.index', $group));

    $duty = $duty->fresh();

    expect($duty->type)->toEqual(DutyType::Cleaning)
        ->and($duty->members)->toHaveCount(2);

    delete(route('groups.duties.destroy', [$group, $duty]))
        ->assertRedirect(route('groups.duties.index', $group));

    expect(Duty::query()->whereKey($duty->id)->exists())->toBeFalse();
});

it('prevents regular members from updating or removing duties', function (): void {
    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = createGroupForDutyTests($owner);

    /** @var User $member */
    $member = User::factory()->member()->createOne();

    GroupMember::query()->create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'role' => 'member',
    ]);

    /** @var Duty $duty */
    $duty = Duty::query()->create([
        'group_id' => $group->id,
        'type' => DutyType::Cooking,
        'starts_on' => now()->toDateString(),
    ]);

    actingAs($member);

    patch(route('groups.duties.update', [$group, $duty]), [
        'type' => DutyType::Cleaning->value,
        'starts_on' => now()->toDateString(),
        'member_ids' => [$member->id],
    ])->assertForbidden();

    delete(route('groups.duties.destroy', [$group, $duty]))
        ->assertForbidden();

    expect($duty->fresh()->type)->toBe(DutyType::Cooking)
        ->and(Duty::query()->whereKey($duty->id)->exists())->toBeTrue();
});
