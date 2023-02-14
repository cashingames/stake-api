<?php

namespace App\Listeners;

use App\Events\AchievementBadgeEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class AchievementBadgeListener
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
        $type = $event->type;
        $game_session = $event->game_session;


        Log::info($user.$type.$game_session);


        // switch to determine
        switch ($type) {
            case 'GAME_PLAYED':
                # code...
                break;

            case 'GAME_BOUGHT':
                # code...
                break;

            case 'REFERRAL':
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
}
