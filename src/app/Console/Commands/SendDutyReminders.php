<?php

namespace App\Console\Commands;

use App\Models\DutySlot;
use App\Notifications\DutyReminderNotification;
use Illuminate\Console\Command;

class SendDutyReminders extends Command
{
    protected $signature = 'duties:send-reminders';

    protected $description = 'Send duty reminders for the day before and same day';

    public function handle(): int
    {
        $tomorrow = now()->addDay()->toDateString();
        $today = now()->toDateString();

        $dayBeforeSlots = DutySlot::query()
            ->with(['duty.group', 'user'])
            ->where('date', $tomorrow)
            ->where('notified_day_before', false)
            ->get();

        foreach ($dayBeforeSlots as $slot) {
            $slot->user->notify(new DutyReminderNotification($slot, 'day_before'));
            $slot->update(['notified_day_before' => true]);
        }

        $sameDaySlots = DutySlot::query()
            ->with(['duty.group', 'user'])
            ->where('date', $today)
            ->where('notified_same_day', false)
            ->get();

        foreach ($sameDaySlots as $slot) {
            $slot->user->notify(new DutyReminderNotification($slot, 'same_day'));
            $slot->update(['notified_same_day' => true]);
        }

        $this->info("Sent {$dayBeforeSlots->count()} day-before and {$sameDaySlots->count()} same-day reminders.");

        return self::SUCCESS;
    }
}
