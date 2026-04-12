<?php

use App\Enums\DutyType;
use App\Models\Duty;
use App\Models\GroupMember;
use App\Models\User;
use App\Services\Groups\CreateHomeGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Notification::fake();
});

it('lists duties for a group member', function (): void {
    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Willow Home']);

    Sanctum::actingAs($owner);

    getJson(route('api.v1.groups.duties.index', $group))
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('creates a cooking duty with members', function (): void {
    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Willow Home']);

    /** @var User $second */
    $second = User::factory()->member()->create();
    GroupMember::query()->create([
        'group_id' => $group->id,
        'user_id' => $second->id,
        'role' => 'member',
    ]);

    Sanctum::actingAs($owner);

    postJson(route('api.v1.groups.duties.store', $group), [
        'type' => DutyType::Cooking->value,
        'starts_on' => now()->toDateString(),
        'member_ids' => [$owner->id, $second->id],
    ])
        ->assertCreated()
        ->assertJsonPath('data.type', 'cooking')
        ->assertJsonCount(2, 'data.members');

    expect(Duty::query()->count())->toBe(1);
});

it('requires cleaning_period_days for cleaning duties', function (): void {
    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Willow Home']);

    Sanctum::actingAs($owner);

    postJson(route('api.v1.groups.duties.store', $group), [
        'type' => DutyType::Cleaning->value,
        'starts_on' => now()->toDateString(),
        'member_ids' => [$owner->id],
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('cleaning_period_days');
});

it('updates a duty', function (): void {
    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Willow Home']);

    Sanctum::actingAs($owner);

    $duty = Duty::query()->create([
        'group_id' => $group->id,
        'type' => DutyType::Cleaning,
        'starts_on' => now()->toDateString(),
        'cleaning_period_days' => 2,
    ]);

    patchJson(route('api.v1.groups.duties.update', [$group, $duty]), [
        'type' => DutyType::Cleaning->value,
        'starts_on' => now()->toDateString(),
        'cleaning_period_days' => 3,
        'member_ids' => [$owner->id],
    ])
        ->assertOk()
        ->assertJsonPath('data.cleaning_period_days', 3);
});

it('deletes a duty', function (): void {
    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Willow Home']);

    Sanctum::actingAs($owner);

    $duty = Duty::query()->create([
        'group_id' => $group->id,
        'type' => DutyType::Cooking,
        'starts_on' => now()->toDateString(),
    ]);

    deleteJson(route('api.v1.groups.duties.destroy', [$group, $duty]))
        ->assertOk();

    expect(Duty::query()->find($duty->id))->toBeNull();
});

it('denies duty management to non-admins', function (): void {
    /** @var User $owner */
    $owner = User::factory()->create();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Willow Home']);

    /** @var User $member */
    $member = User::factory()->member()->create();
    GroupMember::query()->create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'role' => 'member',
    ]);

    Sanctum::actingAs($member);

    postJson(route('api.v1.groups.duties.store', $group), [
        'type' => DutyType::Cooking->value,
        'starts_on' => now()->toDateString(),
        'member_ids' => [$owner->id],
    ])->assertForbidden();
});
