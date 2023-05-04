<?php

namespace App\Notifications;

use App\Enums\PushNotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChallengeStakingRefund extends Notification
{
    use Queueable;

    public $amount;

    /**
     * Create a new notification instance.
     */
    public function __construct($amount)
    {
        $this->amount = $amount;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['fcm', 'database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => "Your challenge staking has been refunded",
            'action_type' => PushNotificationType::Challenge,
            'action_id' => '#'
        ];
    }

    public function toFcm($notifiable): array
    {
        return [
            'title' => "Cashingames Challenge Staking Refund",
            'body' => "Your challenge staking of ₦$this->amount has been refunded",
            'action_type' => PushNotificationType::Challenge,
            'action_id' => '#',
            'unread_notifications_count' => $notifiable->unreadNotifications()->count()
        ];
    }
}
