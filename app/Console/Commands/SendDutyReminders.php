<?php

namespace App\Console\Commands;

use App\Models\DutySlot;
use App\Notifications\DutyReminderNotification;
use Illuminate\Console\Command;

class SendDutyReminders extends Command
{
    protected $signature = 'duties:send-reminders';

    protected $description = 'Send duty reminders for the same day';

    public function handle(): int
    {
        $today = now()->toDateString();

        $sameDaySlots = DutySlot::query()
            ->with(['duty.group', 'user'])
            ->whereDate('date', $today)
            ->where('notified_same_day', false)
            ->get();

        foreach ($sameDaySlots as $slot) {
            $slot->user->notify(new DutyReminderNotification($slot));
            $slot->update(['notified_same_day' => true]);
        }

        $this->info("Sent {$sameDaySlots->count()} same-day reminders.");

        return self::SUCCESS;
    }
}
