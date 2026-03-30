<?php

namespace App\Notifications;

use App\Models\DutySlot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DutyReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly DutySlot $slot,
        private readonly string $timing,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $duty = $this->slot->duty;
        $groupName = $duty->group->name;
        $type = $duty->type->label();
        $dateFormatted = $this->slot->date->toFormattedDateString();

        $subject = $this->timing === 'day_before'
            ? "{$type} duty tomorrow — {$groupName}"
            : "{$type} duty today — {$groupName}";

        $line = $this->timing === 'day_before'
            ? "Your **{$type}** duty for **{$groupName}** is scheduled for tomorrow, **{$dateFormatted}**."
            : "Your **{$type}** duty for **{$groupName}** is today, **{$dateFormatted}**.";

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},")
            ->line($line)
            ->action('View duties', route('groups.duties.index', $duty->group_id));
    }
}
