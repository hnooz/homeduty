<?php

namespace App\Notifications;

use App\Models\GroupInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HomeGroupInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly GroupInvitation $invitation) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $acceptUrl = route('group-invitations.show', $this->invitation);
        $registerUrl = route('register', [
            'invitation' => $this->invitation->token,
            'email' => $this->invitation->email,
        ]);

        return (new MailMessage)
            ->subject('You have been invited to join '.config('app.name'))
            ->greeting('Hello '.$this->invitation->name.',')
            ->line($this->invitation->invitedBy->name.' invited you to join the '.$this->invitation->group->name.' Home Group as '.$this->invitation->role->label().'.')
            ->line('If you already have an account, sign in and accept the invitation using the link below.')
            ->action('Review invitation', $acceptUrl)
            ->line('If you still need an account, register first using this invitation-aware link: '.$registerUrl)
            ->line('This invitation expires in 7 days.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'group_id' => $this->invitation->group_id,
            'group_name' => $this->invitation->group->name,
            'role' => $this->invitation->role->value,
            'token' => $this->invitation->token,
        ];
    }
}
