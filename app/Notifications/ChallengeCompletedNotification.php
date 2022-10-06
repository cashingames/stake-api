<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Challenge;
use Illuminate\Bus\Queueable;
use App\Enums\PushNotificationType;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ChallengeCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $challenge;

    protected $currentPlayer;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Challenge $challenge, $currentPlayer)
    {
        $this->challenge = $challenge;
        $this->currentPlayer = $currentPlayer;
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
            'title' => "Your opponent, {$this->currentPlayer->username} has completed the challenge",
            'action_type' => PushNotificationType::Challenge,
            'action_id' => $this->challenge->id
        ];
    }
}
