<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    /**
     * Mark single or all user notifications as read
     */
    public function readNotification($notificationId){
        if ($notificationId == "all"){
            $this->user->unreadNotifications()->update(['read_at' => now()]);
        }else{
            UserNotification::whereId($notificationId)->update(['read_at' => now()]);
        }

        return $this->sendResponse("Notification marked as read", 'Notification marked as read');
    }
}
