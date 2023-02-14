<?php

namespace App\Listeners;

use App\Events\AchievementBadgeEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class AchievementBadgeEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\AchievementBadgeEvent  $event
     * @return void
     */
    public function handle(AchievementBadgeEvent $event)
    {
        //
        $user = $event->request->user();
        // $user = $event->request;
        $type = $event->type;
        $data = $event->data;

        // switch to determine
        switch ($type) {
            case 'GAME_PLAYED':
                # code...
                $this->gamePlayed($user, $data);
                break;

            case 'GAME_BOUGHT':
                # code...
                break;

            case 'SKIP_BOUGHT':
                # code...
                break;

            case 'TIME_FREEZE_BOUGHT':
                # code...
                break;

            case 'REFERRAL':
                # code...
                break;

            case 'CHALLENGE_STARTED':
                # code...
                break;

            case 'CHALLENGE_ACCEPTED':
                # code...
                break;

            default:
                # code...
                break;
        }
    }

    public function gamePlayed($user, $data){
        Log::info($user.$type.$data."loggChecker");
    }
}
