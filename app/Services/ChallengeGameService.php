<?php

namespace App\Services;

use App\Models\User;
use App\Models\Challenge;
use App\Mail\ChallengeInvite;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Actions\SendPushNotification;
use App\Notifications\ChallengeReceivedNotification;

class ChallengeGameService{

    /**
     * Create a new challenge and send invite notification to the opponent(s)
     * 
     * @param App\User $creator
     * 
     * @param mixed $opponent
     * $opponent can either be a single id, or an array of ids. If an array of ids is supplied, notification will be sent to all opponents involved
     * 
     * @param int $categoryId
     */
    public function createChallenge(User $creator, $opponents, $categoryId){
        if (is_numeric($opponents)){
            $opponents = [$opponents];
        }
        $createdChallenges = [];
        
        foreach ($opponents as $opponentId) {
            $challenge = Challenge::create([
                'status' => 'PENDING',
                'user_id' => $creator->id,
                'category_id' => $categoryId,
                'opponent_id' => $opponentId
            ]);

            $opponent = User::find($opponentId);

            //database notification
            $opponent->notify(new ChallengeReceivedNotification($challenge, $creator));
            //email notification
            Mail::send(new ChallengeInvite($opponent, $challenge));
            //push notification

            dispatch(function() use($creator, $opponent, $challenge){
                $pushAction = new SendPushNotification();
                $pushAction->sendChallengeInviteNotification($creator, $opponent, $challenge);
            });
            
            Log::info("Challenge id : $challenge->id  invite from " . $creator->username . " sent to {$opponent->username}");
            array_push($createdChallenges, $challenge);
        }
        return $createdChallenges;
    }
}