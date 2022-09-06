<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Enums\PushNotificationType;
use App\Models\WalletTransaction;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class WalletFundedNotification extends Notification
{
    use Queueable;

    protected $transaction;
    
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(WalletTransaction $transaction)
    {
        $this->transaction = $transaction;
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
        $formatter = new \NumberFormatter('en_US', \NumberFormatter::DECIMAL);
        $amount = "₦" . $formatter->format($this->transaction->amount);
        return [
            'title' => "Your wallet has been successfully funded with {$amount}",
            
            'action_type' => PushNotificationType::Wallet,
            'action_id' => $this->transaction->id
        ];
    }
}
