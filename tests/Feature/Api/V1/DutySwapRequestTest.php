<?php

use App\Enums\DutySwapRequestStatus;
use App\Enums\DutyType;
use App\Models\Duty;
use App\Models\DutySlot;
use App\Models\DutySwapRequest;
use App\Models\GroupMember;
use App\Models\User;
use App\Services\Groups\CreateHomeGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Notification::fake();
});

function setupApiSwapScenario(): array
{
    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'API Home']);

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

    $duty->members()->attach($owner->id, ['sort_order' => 0]);
    $duty->members()->attach($member->id, ['sort_order' => 1]);

    $ownerSlot = DutySlot::query()->create([
        'duty_id' => $duty->id,
        'user_id' => $owner->id,
        'date' => now()->addDays(2)->toDateString(),
    ]);

    return compact('owner', 'member', 'group', 'duty', 'ownerSlot');
}

it('creates a swap request via API', function (): void {
    ['owner' => $owner, 'member' => $member, 'group' => $group, 'ownerSlot' => $ownerSlot] = setupApiSwapScenario();

    Sanctum::actingAs($owner);

    postJson(route('api.v1.groups.duty-swap-requests.store', $group), [
        'duty_slot_id' => $ownerSlot->id,
        'recipient_id' => $member->id,
        'message' => 'Please swap',
    ])
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.message', 'Please swap');

    expect(DutySwapRequest::query()->count())->toBe(1);
});

it('accepts a swap request via API', function (): void {
    ['owner' => $owner, 'member' => $member, 'group' => $group, 'ownerSlot' => $ownerSlot] = setupApiSwapScenario();

    $swapRequest = DutySwapRequest::query()->create([
        'duty_slot_id' => $ownerSlot->id,
        'requester_id' => $owner->id,
        'recipient_id' => $member->id,
        'status' => DutySwapRequestStatus::Pending,
    ]);

    Sanctum::actingAs($member);

    postJson(route('api.v1.groups.duty-swap-requests.accept', [$group, $swapRequest]))
        ->assertOk()
        ->assertJsonPath('data.status', 'accepted');

    expect($ownerSlot->fresh()->user_id)->toBe($member->id);
});

it('rejects a swap request via API', function (): void {
    ['owner' => $owner, 'member' => $member, 'group' => $group, 'ownerSlot' => $ownerSlot] = setupApiSwapScenario();

    $swapRequest = DutySwapRequest::query()->create([
        'duty_slot_id' => $ownerSlot->id,
        'requester_id' => $owner->id,
        'recipient_id' => $member->id,
        'status' => DutySwapRequestStatus::Pending,
    ]);

    Sanctum::actingAs($member);

    postJson(route('api.v1.groups.duty-swap-requests.reject', [$group, $swapRequest]))
        ->assertOk()
        ->assertJsonPath('data.status', 'rejected');

    expect($ownerSlot->fresh()->user_id)->toBe($owner->id);
});

it('prevents non-recipient from accepting via API', function (): void {
    ['owner' => $owner, 'member' => $member, 'group' => $group, 'ownerSlot' => $ownerSlot] = setupApiSwapScenario();

    $swapRequest = DutySwapRequest::query()->create([
        'duty_slot_id' => $ownerSlot->id,
        'requester_id' => $owner->id,
        'recipient_id' => $member->id,
        'status' => DutySwapRequestStatus::Pending,
    ]);

    Sanctum::actingAs($owner);

    postJson(route('api.v1.groups.duty-swap-requests.accept', [$group, $swapRequest]))
        ->assertForbidden();
});

it('rejects swap request for another members slot via API', function (): void {
    ['owner' => $owner, 'member' => $member, 'group' => $group, 'ownerSlot' => $ownerSlot] = setupApiSwapScenario();

    Sanctum::actingAs($member);

    postJson(route('api.v1.groups.duty-swap-requests.store', $group), [
        'duty_slot_id' => $ownerSlot->id,
        'recipient_id' => $owner->id,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('duty_slot_id');
});
