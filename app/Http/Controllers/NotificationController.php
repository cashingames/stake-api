<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    /**
     * Fetch all notifications
     */
    public function index(Request $request)
    {
        if ($request->header('x-brand-id') == 2) {
            $notifications = $this->user->notifications()->where('data', 'not like', '%CHALLENGE%')->paginate(20);
            return $this->sendResponse($notifications, "Notifications fetched successfully");
        }
        $notifications = $this->user->notifications()->paginate(20);
        return $this->sendResponse($notifications, "Notifications fetched successfully");
    }
    /**
     * Mark single or all user notifications as read
     */
    public function readNotification(Request $request, $notificationId)
    {
        if ($notificationId == "all") {
            if ($request->header('x-brand-id') == 2) {
                foreach ($this->user->unreadNotifications()->where('data', 'not like', '%CHALLENGE%') as $notification) {
                    $notification->update(['read_at' => now()]);
                }
            } else {
                foreach ($this->user->unreadNotifications as $notification) {
                    $notification->update(['read_at' => now()]);
                }
            }
        } else {
            UserNotification::whereId($notificationId)->update(['read_at' => now()]);
        }

        $unreadNotifications = $this->user->unreadNotifications()->count();
        $result = [
            'unreadNotificationsCount' => $unreadNotifications
        ];

        return $this->sendResponse($result, 'Notification marked as read');
    }
}
