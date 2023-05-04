<?php

namespace App\Http\Controllers;

use App\Enums\ClientPlatform;
use App\Models\UserNotification;
use Illuminate\Http\Request;
class NotificationController extends BaseController
{
    /**
     * Fetch all notifications
     */
    public function index( Request $request)
    {
        $notifications = $this->user->notifications();

        return $this->sendResponse($notifications->paginate(20), "Notifications fetched successfully");
    }
    /**
     * Mark single or all user notifications as read
     */
    public function readNotification(Request $request, $notificationId)
    {
        $notificationId == "all" ?
            $this->readAllNotifications() :
            $this->readSingleNotification($notificationId);

        $unreadNotifications = $this->user->unreadNotifications()->count();
        $result = [
            'unreadNotificationsCount' => $unreadNotifications
        ];

        return $this->sendResponse($result, 'Notification marked as read');
    }

    private function readSingleNotification($notificationId)
    {
        UserNotification::whereId($notificationId)->update(['read_at' => now()]);
    }

    private function readAllNotifications()
    {

        $query = $this->user->unreadNotifications();

        $query->update(['read_at' => now()]);
    }
}
