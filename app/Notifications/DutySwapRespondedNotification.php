<?php

namespace App\Notifications;

use App\Enums\DutySwapRequestStatus;
use App\Models\DutySwapRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DutySwapRespondedNotification extends Notification implements ShouldQueue
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
        $recipientName = $this->swapRequest->recipient->name;
        $dutyType = $this->swapRequest->dutySlot->duty->type->label();
        $date = $this->swapRequest->dutySlot->date->toFormattedDateString();
        $accepted = $this->swapRequest->status === DutySwapRequestStatus::Accepted;

        $mail = (new MailMessage)
            ->subject("Duty swap {$this->swapRequest->status->label()} by {$recipientName}")
            ->greeting("Hello {$notifiable->name},");

        if ($accepted) {
            $mail->line("{$recipientName} has **accepted** your request to take the **{$dutyType}** duty on **{$date}**.")
                ->line('The duty has been reassigned.');
        } else {
            $mail->line("{$recipientName} has **declined** your request to swap the **{$dutyType}** duty on **{$date}**.");
        }

        return $mail->action('View duties', route('dashboard'));
    }
}
