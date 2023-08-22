<?php

namespace App\Notifications;

use App\Enums\PushNotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CashdropDroppedNotification extends Notification
{
    use Queueable;

    public $username, $cashdropName, $amount;
    /**
     * Create a new notification instance.
     */
    public function __construct($username, $cashdropName, $amount)
    {
        $this->username = $username;
        $this->cashdropName = $cashdropName;
        $this->amount = $amount;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => "$this->username just won ₦$this->amount of $this->cashdropName cashdrop",
            'action_type' => PushNotificationType::Cashdrop->value,
            'action_id' => '#'
        ];
    }
}
