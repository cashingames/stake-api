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

use Illuminate\Support\Facades\Event;
use App\Events\AchievementBadgeEvent;
use App\Enums\AchievementType;

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

        if (!$request->has('opponentId') || !$request->has('categoryId')) {

            return $this->sendError('Friend or category not found', 'Friend or category not found');
        }

        if ($request->has('staking_amount') && is_numeric($request->staking_amount)) {
            if (is_array($request->opponentId) && $this->user->wallet->non_withdrawable_balance < ($request->staking_amount * count($request->opponentId))) {
                return $this->sendError('Insufficient wallet balance', 'Insufficient wallet balance to stake against all opponents');
            }
            if ($this->user->wallet->non_withdrawable_balance < $request->staking_amount) {
                return $this->sendError('Insufficient wallet balance', 'Insufficient wallet balance');
            }

            if ($request->staking_amount < config('trivia.minimum_challenge_staking_amount')) {
                return $this->sendError("The minimum amount you can stake is " . config('trivia.minimum_challenge_staking_amount'), "The minimum amount you can stake is " . config('trivia.minimum_challenge_staking_amount'));
            }
        }



        $challenges = $challengeGameService->createChallenge($this->user, $request->opponentId, $request->categoryId);

        if (is_null($challenges)) {
            return $this->sendError("Category is not available", "Category is not available");
        }

        if ($request->has('staking_amount') && FeatureFlag::isEnabled(FeatureFlags::CHALLENGE_GAME_STAKING)) {
            foreach ($challenges as $challenge) {
                $staking = new StakingService($this->user, 'challenge');
                $stakingId = $staking->stakeAmount($request->staking_amount);
                $staking->createChallengeStaking($stakingId, $challenge->id);
            }
        }

        // call the event listener
        Event::dispatch(new AchievementBadgeEvent($request, AchievementType::CHALLENGE_STARTED, null));

        return $this->sendResponse('Invite Sent', 'Invite Sent');
    }
}
