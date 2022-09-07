<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    /**
     * Fetch all notifications
     */
    public function index(Request $request){
        $notifications = $this->user->unreadNotifications()->paginate(20);
        return $this->sendResponse($notifications, "Notifications fetched successfully");
    }
    /**
     * Mark single or all user notifications as read
     */
    public function readNotification($notificationId){
        if ($notificationId == "all"){
            $this->user->unreadNotifications()->update(['read_at' => now()]);
        }else{
            UserNotification::whereId($notificationId)->update(['read_at' => now()]);
        }

        $unreadNotifications = $this->user->unreadNotifications()->count();
        $result = [
            'unreadNotificationsCount' => $unreadNotifications
        ];

        return $this->sendResponse($result, 'Notification marked as read');
    }
}
