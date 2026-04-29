<?php

namespace App\Notifications;

use App\Models\DutySwapRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DutySwapRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly DutySwapRequest $swapRequest,
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
        $requesterName = $this->swapRequest->requester->name;
        $dutyType = $this->swapRequest->dutySlot->duty->type->label();
        $date = $this->swapRequest->dutySlot->date->toFormattedDateString();
        $groupId = $this->swapRequest->dutySlot->duty->group_id;

        return (new MailMessage)
            ->subject("Duty swap request from {$requesterName}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$requesterName} has requested you to take their **{$dutyType}** duty on **{$date}**.")
            ->when($this->swapRequest->message, fn (MailMessage $mail) => $mail->line("Message: \"{$this->swapRequest->message}\""))
            ->line('You can accept or reject this request from your dashboard.')
            ->action('View dashboard', route('dashboard'));
    }
}
