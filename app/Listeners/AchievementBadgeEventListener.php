<?php

namespace App\Listeners;

use App\Events\AchievementBadgeEvent;
use App\Models\AchievementBadge;

use App\Enums\FeatureFlags;
use App\Services\AchievementBadgeEventService;
use App\Services\FeatureFlag;

class AchievementBadgeEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */

    public $user;

    /**
     * Handle the event.
     *
     * @param  \App\Events\AchievementBadgeEvent  $event
     * @return void
     */
    public function handle(AchievementBadgeEvent $event)
    {
        if(FeatureFlag::isEnabled(FeatureFlags::ACHIEVEMENT_BADGES)){
            $AchievementType = $event->AchievementType;

            $user = null;
            if(($AchievementType === "GAME_BOUGHT") || ($AchievementType === "BOOST_BOUGHT") || ($AchievementType === "REFERRAL") ){
                $user = $event->request;
            }else{
                $user = $event->request->user();
            }
            $this->user = $user;
            // $user = $event->request;
            $payload = $event->payload;

            $achievementBadgeService = new AchievementBadgeEventService($user);

            // switch to determine
            switch ($AchievementType) {
                case 'GAME_PLAYED':
                    # code...
                    $achievementBadgeService->gamePlayed($user, $payload);
                    break;

                case 'GAME_BOUGHT':
                    # code...
                    $achievementBadgeService->gameBought($user, $payload);
                    break;

                case 'BOOST_BOUGHT':
                    # code...
                    $achievementBadgeService->boostBought($user, $payload);
                    break;

                case 'REFERRAL':
                    # code...
                    $achievementBadgeService->referralKing($user);
                    break;

                case 'CHALLENGE_STARTED':
                    # code...
                    $achievementBadgeService->challengeStarted($user);
                    break;

                case 'CHALLENGE_ACCEPTED':
                    # code...
                    $achievementBadgeService->challengeAccepted($user);
                    break;

                default:
                    # code...
                    break;
            }
        }
    }



}
