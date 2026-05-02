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

    public function __construct(private readonly DutySlot $slot) {}

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

        return (new MailMessage)
            ->subject("{$type} duty today — {$groupName}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your **{$type}** duty for **{$groupName}** is today, **{$dateFormatted}**.")
            ->action('View duties', route('groups.duties.index', $duty->group_id));
    }
}
