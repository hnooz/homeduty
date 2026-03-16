<?php

use App\Enums\DutyFrequency;
use App\Models\Duty;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Services\Groups\CreateHomeGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
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

it('allows a home group owner to create and assign a duty', function (): void {
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
        'name' => 'Kitchen reset',
        'description' => 'Wipe the counters and put everything back in place.',
        'frequency' => DutyFrequency::Daily->value,
        'starts_on' => now()->toDateString(),
        'assigned_user_id' => $member->id,
    ])->assertRedirect(route('groups.duties.index', $group));

    $duty = Duty::query()->where('name', 'Kitchen reset')->firstOrFail();

    expect($duty->group_id)->toBe($group->id)
        ->and($duty->assigned_user_id)->toBe($member->id)
        ->and($duty->frequency)->toBe(DutyFrequency::Daily);
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
        'name' => 'Hallway sweep',
        'frequency' => DutyFrequency::Weekly->value,
        'starts_on' => now()->toDateString(),
    ])->assertForbidden();

    expect(Duty::query()->where('name', 'Hallway sweep')->exists())->toBeFalse();
});

it('rejects an assignee who does not belong to the home group', function (): void {
    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = createGroupForDutyTests($owner);

    /** @var User $outsider */
    $outsider = User::factory()->member()->createOne();

    actingAs($owner);

    post(route('groups.duties.store', $group), [
        'name' => 'Bathroom deep clean',
        'frequency' => DutyFrequency::Monthly->value,
        'starts_on' => now()->toDateString(),
        'assigned_user_id' => $outsider->id,
    ])->assertSessionHasErrors('assigned_user_id');

    expect(Duty::query()->where('name', 'Bathroom deep clean')->exists())->toBeFalse();
});
