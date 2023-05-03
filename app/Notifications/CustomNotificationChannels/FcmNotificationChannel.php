<?php

namespace App\Notifications\CustomNotificationChannels;

use App\Models\FcmPushSubscription;
use App\Services\Firebase\CloudMessagingService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class FcmNotificationChannel
{

    public function send($notifiable, Notification $notification)
    {

        if (!method_exists($notification, 'toFcm')) {
            Log::info("toFcm() does not exist on the notification class ");
            return;
        }

        $pushService = new CloudMessagingService(config('services.firebase.server_key'));
        $recipient = FcmPushSubscription::where('user_id', $notifiable->id)->latest()->first();

        if (is_null($recipient)) {
            return;
        }

        $data = $notification->toFcm($notifiable);

        $pushService->setNotification(
            [
                'title' => $data['title'],
                'body' => $data['body']
            ]
        )
            ->setData(
                $data
            )
            ->setTo($recipient->device_token)
            ->send();
        return Log::info("Challenge staking refund push notification sent to: " . $notifiable->username);
    }
}
