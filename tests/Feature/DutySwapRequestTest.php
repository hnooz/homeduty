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

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

function setupSwapScenario(): array
{
    Notification::fake();

    /** @var User $owner */
    $owner = User::factory()->createOne();
    $group = app(CreateHomeGroup::class)->handle($owner, ['name' => 'Test Home']);

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

    $memberSlot = DutySlot::query()->create([
        'duty_id' => $duty->id,
        'user_id' => $member->id,
        'date' => now()->addDays(3)->toDateString(),
    ]);

    return compact('owner', 'member', 'group', 'duty', 'ownerSlot', 'memberSlot');
}

it('allows a member to request a swap for their own future slot', function (): void {
    ['owner' => $owner, 'member' => $member, 'group' => $group, 'ownerSlot' => $ownerSlot] = setupSwapScenario();

    actingAs($owner);

    post(route('groups.duty-swap-requests.store', $group), [
        'duty_slot_id' => $ownerSlot->id,
        'recipient_id' => $member->id,
        'message' => 'I have an appointment',
    ])->assertRedirect(route('groups.duties.index', $group));

    expect(DutySwapRequest::query()->count())->toBe(1);

    $swapRequest = DutySwapRequest::query()->first();

    expect($swapRequest->requester_id)->toBe($owner->id)
        ->and($swapRequest->recipient_id)->toBe($member->id)
        ->and($swapRequest->status)->toBe(DutySwapRequestStatus::Pending)
        ->and($swapRequest->message)->toBe('I have an appointment');
});

it('prevents requesting a swap for another members slot', function (): void {
    ['owner' => $owner, 'member' => $member, 'group' => $group, 'memberSlot' => $memberSlot] = setupSwapScenario();

    actingAs($owner);

    post(route('groups.duty-swap-requests.store', $group), [
        'duty_slot_id' => $memberSlot->id,
        'recipient_id' => $member->id,
    ])->assertSessionHasErrors('duty_slot_id');

    expect(DutySwapRequest::query()->count())->toBe(0);
});

it('prevents requesting a swap for a past slot', function (): void {
    ['owner' => $owner, 'member' => $member, 'group' => $group, 'duty' => $duty] = setupSwapScenario();

    $pastSlot = DutySlot::query()->create([
        'duty_id' => $duty->id,
        'user_id' => $owner->id,
        'date' => now()->subDay()->toDateString(),
    ]);

    actingAs($owner);

    post(route('groups.duty-swap-requests.store', $group), [
        'duty_slot_id' => $pastSlot->id,
        'recipient_id' => $member->id,
    ])->assertSessionHasErrors('duty_slot_id');

    expect(DutySwapRequest::query()->count())->toBe(0);
});

it('prevents duplicate pending swap requests for the same slot', function (): void {
    ['owner' => $owner, 'member' => $member, 'group' => $group, 'ownerSlot' => $ownerSlot] = setupSwapScenario();

    DutySwapRequest::query()->create([
        'duty_slot_id' => $ownerSlot->id,
        'requester_id' => $owner->id,
        'recipient_id' => $member->id,
        'status' => DutySwapRequestStatus::Pending,
    ]);

    actingAs($owner);

    post(route('groups.duty-swap-requests.store', $group), [
        'duty_slot_id' => $ownerSlot->id,
        'recipient_id' => $member->id,
    ])->assertSessionHasErrors('duty_slot_id');

    expect(DutySwapRequest::query()->count())->toBe(1);
});

it('prevents requesting a swap with a non-rotation member', function (): void {
    ['owner' => $owner, 'group' => $group, 'ownerSlot' => $ownerSlot] = setupSwapScenario();

    /** @var User $outsider */
    $outsider = User::factory()->member()->createOne();

    GroupMember::query()->create([
        'group_id' => $group->id,
        'user_id' => $outsider->id,
        'role' => 'member',
    ]);

    actingAs($owner);

    post(route('groups.duty-swap-requests.store', $group), [
        'duty_slot_id' => $ownerSlot->id,
        'recipient_id' => $outsider->id,
    ])->assertSessionHasErrors('recipient_id');
});

it('allows the recipient to accept a swap request', function (): void {
    ['owner' => $owner, 'member' => $member, 'group' => $group, 'ownerSlot' => $ownerSlot] = setupSwapScenario();

    $swapRequest = DutySwapRequest::query()->create([
        'duty_slot_id' => $ownerSlot->id,
        'requester_id' => $owner->id,
        'recipient_id' => $member->id,
        'status' => DutySwapRequestStatus::Pending,
    ]);

    actingAs($member);

    post(route('groups.duty-swap-requests.accept', [$group, $swapRequest]))
        ->assertRedirect(route('dashboard'));

    $swapRequest->refresh();
    $ownerSlot->refresh();

    expect($swapRequest->status)->toBe(DutySwapRequestStatus::Accepted)
        ->and($swapRequest->responded_at)->not->toBeNull()
        ->and($ownerSlot->user_id)->toBe($member->id);
});

it('allows the recipient to reject a swap request', function (): void {
    ['owner' => $owner, 'member' => $member, 'group' => $group, 'ownerSlot' => $ownerSlot] = setupSwapScenario();

    $swapRequest = DutySwapRequest::query()->create([
        'duty_slot_id' => $ownerSlot->id,
        'requester_id' => $owner->id,
        'recipient_id' => $member->id,
        'status' => DutySwapRequestStatus::Pending,
    ]);

    actingAs($member);

    post(route('groups.duty-swap-requests.reject', [$group, $swapRequest]))
        ->assertRedirect(route('dashboard'));

    $swapRequest->refresh();
    $ownerSlot->refresh();

    expect($swapRequest->status)->toBe(DutySwapRequestStatus::Rejected)
        ->and($swapRequest->responded_at)->not->toBeNull()
        ->and($ownerSlot->user_id)->toBe($owner->id);
});

it('prevents non-recipients from accepting a swap request', function (): void {
    ['owner' => $owner, 'member' => $member, 'group' => $group, 'ownerSlot' => $ownerSlot] = setupSwapScenario();

    $swapRequest = DutySwapRequest::query()->create([
        'duty_slot_id' => $ownerSlot->id,
        'requester_id' => $owner->id,
        'recipient_id' => $member->id,
        'status' => DutySwapRequestStatus::Pending,
    ]);

    actingAs($owner);

    post(route('groups.duty-swap-requests.accept', [$group, $swapRequest]))
        ->assertForbidden();

    $swapRequest->refresh();

    expect($swapRequest->status)->toBe(DutySwapRequestStatus::Pending);
});

it('prevents responding to an already resolved swap request', function (): void {
    ['owner' => $owner, 'member' => $member, 'group' => $group, 'ownerSlot' => $ownerSlot] = setupSwapScenario();

    $swapRequest = DutySwapRequest::query()->create([
        'duty_slot_id' => $ownerSlot->id,
        'requester_id' => $owner->id,
        'recipient_id' => $member->id,
        'status' => DutySwapRequestStatus::Accepted,
        'responded_at' => now(),
    ]);

    actingAs($member);

    post(route('groups.duty-swap-requests.accept', [$group, $swapRequest]))
        ->assertStatus(409);
});
