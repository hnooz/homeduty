<?php

use App\Enums\DutyType;
use App\Models\Duty;
use App\Models\DutySlot;
use App\Models\Group;
use App\Models\User;
use App\Notifications\DutyReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('sends only same-day reminders', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $duty = Duty::factory()->create([
        'group_id' => $group->id,
        'type' => DutyType::Cooking,
        'starts_on' => now()->toDateString(),
    ]);

    $todaySlot = DutySlot::create([
        'duty_id' => $duty->id,
        'user_id' => $owner->id,
        'date' => now()->toDateString(),
    ]);

    DutySlot::create([
        'duty_id' => $duty->id,
        'user_id' => $owner->id,
        'date' => now()->addDay()->toDateString(),
    ]);

    $this->artisan('duties:send-reminders')->assertSuccessful();

    Notification::assertSentTo($owner, DutyReminderNotification::class, 1);
    expect($todaySlot->fresh()->notified_same_day)->toBeTrue();
});

it('does not resend same-day reminder when already notified', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $duty = Duty::factory()->create([
        'group_id' => $group->id,
        'type' => DutyType::Cooking,
        'starts_on' => now()->toDateString(),
    ]);

    DutySlot::create([
        'duty_id' => $duty->id,
        'user_id' => $owner->id,
        'date' => now()->toDateString(),
        'notified_same_day' => true,
    ]);

    $this->artisan('duties:send-reminders')->assertSuccessful();

    Notification::assertNothingSent();
});
