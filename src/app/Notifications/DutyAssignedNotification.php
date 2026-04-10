<?php

namespace App\Notifications;

use App\Models\Duty;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class DutyAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Duty $duty,
        private readonly \DateTimeInterface $date,
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
        $groupName = $this->duty->group->name;
        $type = $this->duty->type->label();

        return (new MailMessage)
            ->subject("{$type} duty assigned — {$groupName}")
            ->greeting("Hello {$notifiable->name},")
            ->line("You have been assigned to **{$type}** duty for **{$groupName}**.")
            ->line('Your first scheduled date is **'.Carbon::parse($this->date)->toFormattedDateString().'**.')
            ->line('You will receive reminders the day before and on the day of your duty.')
            ->action('View duties', route('groups.duties.index', $this->duty->group_id));
    }
}
