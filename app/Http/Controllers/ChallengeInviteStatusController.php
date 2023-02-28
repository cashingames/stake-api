<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Actions\SendPushNotification;
use App\Enums\FeatureFlags;
use App\Mail\RespondToChallengeInvite;
use App\Http\ResponseHelpers\ChallengeDetailsResponse;
use App\Notifications\ChallengeStatusUpdateNotification;
use App\Services\FeatureFlag;
use App\Services\StakingService;

use Illuminate\Support\Facades\Event;
use App\Events\AchievementBadgeEvent;
use App\Enums\AchievementType;

class ChallengeInviteStatusController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        if (!$request->has('challenge_id')) {
            return $this->sendError('Challenge info not found', 'Challenge info not found');
        }
        $status = $request->status == 1 ? "ACCEPTED" : "DECLINED";

        if (FeatureFlag::isEnabled(FeatureFlags::CHALLENGE_GAME_STAKING)){
            $challenge = Challenge::findOrFail($request->challenge_id);
            if (count($challenge->stakings) > 0){
                if ($this->user->wallet->non_withdrawable_balance < $challenge->stakings()->first()->staking->amount_staked){
                    return $this->sendError('Insufficient wallet balance', 'You do not have enough balance to accept this challenge');
                }
                $staking = $challenge->stakings()->first()->staking;
                $stakingService = new StakingService($this->user, 'challenge');
                $stakingId = $stakingService->stakeAmount($staking->amount_staked);
                $stakingService->createChallengeStaking($stakingId, $challenge->id);
            }
        }
        $updatedChallenge = Challenge::changeChallengeStatus($status, $request->challenge_id);

        $player = User::find($updatedChallenge->user_id);

        Log::info("Challenge with ID: " . $request->challenge_id . "  has been " . $status . " by " . $this->user->username);

        Mail::send(new RespondToChallengeInvite($status, $player, $request->challenge_id));


        $pushAction = new SendPushNotification();
        $pushAction->sendChallengeStatusChangeNotification($player, $this->user, $updatedChallenge, $status);

        $player->notify(new ChallengeStatusUpdateNotification($updatedChallenge, $status));

        Log::info("Challenge $request->challenge_id  response has been sent from " . $this->user->username);

        if($status == "ACCEPTED"){
            // call the event listener
        Event::dispatch(new AchievementBadgeEvent($request, AchievementType::CHALLENGE_ACCEPTED, null));
        }

        return $this->sendResponse('Response email sent', 'Response email sent');
    }
}
