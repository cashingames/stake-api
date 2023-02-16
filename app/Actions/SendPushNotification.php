<?php

namespace App\Actions;

use App\Enums\PushNotificationType;
use App\Models\FcmPushSubscription;
use App\Models\LiveTrivia;
use App\Services\Firebase\CloudMessagingService;
use App\Traits\Utils\DateUtils;
use Illuminate\Support\Facades\Log;

class SendPushNotification
{

    use DateUtils;
    /**
     * @var App\Services\Firebase\CloudMessaging
     */
    public $pushService;

    public function __construct()
    {
        $this->pushService = new CloudMessagingService(config('services.firebase.server_key'));
    }

    public function sendChallengeInviteNotification($sender, $opponent, $challenge)
    {
        $recipient = FcmPushSubscription::where('user_id', $opponent->id)->latest()->first();
        if (is_null($recipient)) {
            return;
        }
        $this->pushService->setNotification(
            [
                'title' => "Cashingames Invitation! : Play a Challenge Game!",
                'body' => "Your friend, {$sender->username} has just sent you a challenge invite"
            ]
        )
            ->setData(
                [

                    'title' => "Cashingames Invitation! : Play a Challenge Game!",
                    'body' => "Your friend, {$sender->username} has just sent you a challenge invite",
                    'action_type' => PushNotificationType::Challenge,
                    'action_id' => $challenge->id,
                    'unread_notifications_count' => $opponent->unreadNotifications()->count()

                ]
            )
            ->setTo($recipient->device_token)
            ->send();
        Log::info("Challenge invitation push notification sent to: " . $opponent->username . " from " . $sender->username);
    }

    public function sendChallengeStatusChangeNotification($player, $opponent, $challenge, $status)
    {
        $recipient = FcmPushSubscription::where('user_id', $player->id)->latest()->first();
        if (is_null($recipient)) {
            return;
        }

        $this->pushService->setNotification(
            [
                'title' => "Cashingames Challenge Status Update",
                'body' => "Your opponent, {$opponent->username} has {$status} your invite"
            ]
        )
            ->setData(
                [
                    'title' => "Challenge Status Update",
                    'body' => "Your opponent, {$opponent->username} has {$status} your invite",
                    'action_type' => PushNotificationType::Challenge,
                    'action_id' => $challenge->id,
                    'unread_notifications_count' => $player->unreadNotifications()->count()
                ]
            )
            ->setTo($recipient->device_token)
            ->send();
        Log::info("Challenge status update push notification sent to: " . $player->username . " from " . $opponent->username);
    }
    public function sendChallengeCompletedNotification($user, $challenge)
    {

        if ($user->id == $challenge->user_id) {
            $recipient = $challenge->opponent;
        } else {
            $recipient = $challenge->users;
        }
        $device_token = FcmPushSubscription::where('user_id', $recipient->id)->latest()->first();
        if (is_null($device_token)) {
            return;
        }
        $this->pushService->setNotification(
            [
                'title' => "Cashingames Challenge Completed!",
                'body' => "Your opponent, {$user->username} has completed the challenge, check the scores now"
            ]
        )
            ->setData(
                [

                    'title' => "Challenge Completed!",
                    'body' => "Your opponent, {$user->username} has completed the challenge, check the scores now",
                    'action_type' => PushNotificationType::Challenge,
                    'action_id' => $challenge->id,
                    'unread_notifications_count' => $recipient->unreadNotifications()->count()

                ]
            )
            ->setTo($device_token->device_token)
            ->send();

        Log::info("Challenge invitation push notification sent to: " . $recipient->username . " from " . $user->username);
    }

    public function sendSpecialHourOddsNotification($user)
    {
        $device_token = FcmPushSubscription::where('user_id', $user->id)->latest()->first();
        if (is_null($device_token)) {
            return;
        }

        $this->pushService->setNotification(
            [
                'title' => "Special Hour: Play now and win more",
                'body' => "Play a game now and increase your odds of winning by x1.5"
            ]
        )
            ->setData(
                [

                    'title' => "Special Hour: Play now and win more",
                    'body' => "Play a game now and increase your odds of winning by x1.5",
                    'action_type' => "#",
                    'action_id' => "#"

                ]
            )
            ->setTo($device_token->device_token)
            ->send();
    }

    public function sendliveTriviaNotification($device, $time)
    {
        $this->pushService->setNotification(
            [
                'title' => "Live Trivia Alert ! : Play $time !",
                'body' => "Play this live trivia and stand a chance to win cash!"
            ]
        )
            ->setData(
                [

                    'title' => "Live Trivia Alert ! : Play $time !",
                    'body' => "Play this live trivia and stand a chance to win cash!",
                    'action_type' => "#",
                    'action_id' => "#"

                ]
            )
            ->setTo($device->device_token)
            ->send();
    }

    public function sendChallengeStakingRefundNotification($player, $challenge)
    {
        $recipient = FcmPushSubscription::where('user_id', $player->id)->latest()->first();
        if (is_null($recipient)) {
            return;
        }

        $this->pushService->setNotification(
            [
                'title' => "Cashingames Challenge Staking Refund",
                'body' => "Your challenge staking has been refunded because your opponent failed to respond"
            ]
        )
            ->setData(
                [
                    'title' => "Challenge Staking Refund",
                    'body' => "Your challenge staking has been refunded because your opponent failed to respond",
                    'action_type' => PushNotificationType::Challenge,
                    'action_id' => $challenge->id,
                    'unread_notifications_count' => $player->unreadNotifications()->count()
                ]
            )
            ->setTo($recipient->device_token)
            ->send();
        Log::info("Challenge staking refund push notification sent to: " . $player->username );
    }

    public function sendDailyBonusGamesNotification($device)
    {
        $this->pushService->setNotification(
            [
                'title' => "You have been awarded free games!",
                'body' => "Play now and stand a chance to win cash!"
            ]
        )
            ->setData(
                [

                    'title' => "You have been awarded free games!",
                    'body' => "Play now and stand a chance to win cash!",
                    'action_type' => "#",
                    'action_id' => "#"

                ]
            )
            ->setTo($device->device_token)
            ->send();
    }

    public function sendBoostsReminderNotification($user)
    {
        $device_token = FcmPushSubscription::where('user_id', $user->id)->latest()->first();
        if (is_null($device_token)) {
            return;
        }

        $this->pushService->setNotification(
            [
                'title' => "Your boosts are your super powers!",
                'body' => "Use boosts to stand a better chance at winning!"
            ]
        )
            ->setData(
                [

                    'title' => "Your boosts are your super powers!",
                    'body' => "Use boosts to stand a better chance at winning!",
                    'action_type' => "#",
                    'action_id' => "#"

                ]
            )
            ->setTo($device_token->device_token)
            ->send();
    }

    public function sendInAppActivityNotification($user, $message)
    {
        $device_token = FcmPushSubscription::where('user_id', $user->id)->latest()->first();
        if (is_null($device_token)) {
            return;
        }

        $this->pushService->setNotification(
            [
                'title' => "Hi there!",
                'body' => $message
            ]
        )
            ->setData(
                [

                    'title' => "Hi there!",
                    'body' => $message,
                    'action_id' => '#',
                    'unread_notifications_count' => $user->unreadNotifications()->count()

                ]
            )
            ->setTo($device_token->device_token)
            ->send();
    }

    // public function sendReferralBonusNotification($user)
    // {
    //     $device_token = FcmPushSubscription::where('user_id', $user->id)->latest()->first();
    //     if (is_null($device_token)) {
    //         Log::info('Logger: no device token for');
    //         Log::info($user);
    //         return;
    //     }

    //     $this->pushService->setNotification(
    //         [
    //             'title' => "You've Been Rewarded For Referral!",
    //             'body' => "Bonus Plan of 2 Games has been rewarded for your referralg!"
    //         ]
    //     )
    //         ->setData(
    //             [

    //                 'title' => "You've Been Rewarded For Referral!",
    //                 'body' => "Bonus Plan of 2 Games has been rewarded for your referral!",
    //                 'action_type' => "#",
    //                 'action_id' => "#"

    //             ]
    //         )
    //         ->setTo($device_token->device_token)
    //         ->send();

    //     Log::info('Logger: FCM Sent TO-> '.$device_token->device_token);
    // }

}
