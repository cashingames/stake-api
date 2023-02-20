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
    public function index(ClientPlatform $platform, Request $request)
    {
        $notifications = $this->user->notifications();
        if ($platform == ClientPlatform::StakingMobileWeb) {
            $notifications->where('data', 'not like', '%CHALLENGE%');
        }

        return $this->sendResponse($notifications->paginate(20), "Notifications fetched successfully");
    }
    /**
     * Mark single or all user notifications as read
     */
    public function readNotification(ClientPlatform $platform, Request $request, $notificationId)
    {
        $notificationId == "all" ?
            $this->readAllNotifications($platform) :
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

    private function readAllNotifications(ClientPlatform $platform)
    {

        if ($platform == ClientPlatform::StakingMobileWeb) {
            $this->user->unreadNotifications()->where('data', 'not like', '%CHALLENGE%')->update(['read_at' => now()]);
        } else {
            $this->user->unreadNotifications()->update(['read_at' => now()]);
        }

        $this->user->unreadNotifications()->update(['read_at' => now()]);
    }
}
