<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Enums\PushNotificationType;
use App\Models\Challenge;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ChallengeReceivedNotification extends Notification
{
    use Queueable;

    public $challenge;

    public $sender;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Challenge $challenge, User $sender)
    {
        $this->challenge = $challenge;
        $this->sender = $sender;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => "You have received a challenge invitation from {$this->sender->username}",
            'action_type' => PushNotificationType::Challenge,
            'action_id' => $this->challenge->id
        ];
    }
}