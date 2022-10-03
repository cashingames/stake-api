<?php

namespace App\Http\Controllers;

use App\Actions\SendPushNotification;
use App\Enums\FeatureFlags;
use App\Mail\ChallengeInvite;
use App\Models\Challenge;
use App\Models\User;
use App\Notifications\ChallengeReceivedNotification;
use App\Services\ChallengeGameService;
use App\Services\FeatureFlag;
use App\Services\Firebase\CloudMessagingService;
use App\Services\StakingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendChallengeInviteController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, ChallengeGameService $challengeGameService)
    {   

        if (!$request->has('opponentId') || !$request->has('categoryId') ) {
            
            return $this->sendError('Friend or category not found', 'Friend or category not found');

        }

        if ($request->has('staking_amount') && is_numeric($request->staking_amount)) {
            if (is_array($request->opponentId) && $this->user->wallet->non_withdrawable_balance < ($request->staking_amount * count($request->opponentId))){
                return $this->sendError('Insufficient wallet balance', 'Insufficient wallet balance to stake against all opponents');
            }
            if ($this->user->wallet->non_withdrawable_balance < $request->staking_amount){
                return $this->sendError('Insufficient wallet balance', 'Insufficient wallet balance');
            }        
        }


        
        $challenges = $challengeGameService->createChallenge($this->user, $request->opponentId, $request->categoryId);

        if ($request->has('staking_amount') && FeatureFlag::isEnabled(FeatureFlags::CHALLENGE_GAME_STAKING)){
            foreach($challenges as $challenge){
                $staking = new StakingService($this->user);
                $stakingId = $staking->stakeAmount($request->staking_amount);
                $staking->createChallengeStaking($stakingId, $challenge->id);
            }
        }
        
        // $challenge = Challenge::create([
        //     'status' => 'PENDING',
        //     'user_id' => $this->user->id,
        //     'category_id' => $request->categoryId,
        //     'opponent_id' => $request->opponentId
        // ]);

        // $opponent = User::find($request->opponentId);
        // Mail::send(new ChallengeInvite($opponent, $challenge->id));

        
        // $pushAction = new SendPushNotification();
        // $pushAction->sendChallengeInviteNotification($this->user, $opponent, $challenge);
        
        // $opponent->notify(new ChallengeReceivedNotification($challenge, $this->user));

        // Log::info("Challenge id : $challenge->id  invite from " . $this->user->username . " sent" );
        
        return $this->sendResponse('Invite Sent', 'Invite Sent');
      
    }

}
