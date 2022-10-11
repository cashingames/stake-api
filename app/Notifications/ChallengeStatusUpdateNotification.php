<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Enums\PushNotificationType;
use App\Models\Challenge;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ChallengeStatusUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $challenge;

    protected $newStatus;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Challenge $challenge, $newStatus)
    {
        $this->challenge = $challenge;
        $this->newStatus = $newStatus;
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
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => "Your opponent, {$this->challenge->opponent->username} has {$this->newStatus} your challenge invitation",
            'action_type' => PushNotificationType::Challenge,
            'action_id' => $this->challenge->id
        ];
    }
}
