<?php

namespace App\Http\Controllers\PlayGame;

use App\Http\Controllers\BaseController;
use App\Models\GameSession;
use App\Enums\GameType;
use App\Actions\SendPushNotification;
use App\Http\Requests\StartSinglePlayerRequest;
use App\Services\PlayGame\PlayGameServiceFactory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use stdClass;

class StartSinglePlayerGameController extends BaseController
{

    public function __invoke(
        Request $request,
        StartSinglePlayerRequest $reqeuestModel,
        GameType $customType,
        PlayGameServiceFactory $gameService
    )
    {
        $validated = $reqeuestModel->validated();
        $validatedRequest = (object) $validated;

        $startResponse = $gameService->startGame($validatedRequest);

        //@TODO: Handle business error states in the services
        if (count($startResponse->questions) < 10 && $customType != GameType::LiveTrivia) {
            return $this->sendError(
                'Category not available for now, try again later',
                'Category not available for now, try again later'
            );
        }

        $this->giftReferrerOnFirstGame();

        $result = $this->formatResponse($startResponse->gameSession, $startResponse->questions);
        return $this->sendResponse($result, 'Game Started');
    }

    private function giftReferrerOnFirstGame()
    {
        if (GameSession::where('user_id', $this->user->id)->count() > 1) {
            Log::info($this->user->username . ' has more than 1 game played already, so no referrer bonus check');
            return;
        }

        $referrerProfile = $this->user->profile->getReferrerProfile();

        if ($referrerProfile === null) {
            Log::info('This user has no referrer: ' . $this->user->username . " referrer_code " . $this->user->profile->referrer);
            return;
        }

        if (
            config('trivia.bonus.enabled') &&
            config('trivia.bonus.signup.referral') &&
            config('trivia.bonus.signup.referral_on_first_game') &&
            isset($referrerProfile)
        ) {

            Log::info('Giving : ' . $this->user->profile->referrer . " bonus for " . $this->user->username);
            Log::info($referrerProfile);

            $plan_count = config('trivia.bonus.signup.referral_on_signup_bonus_amount');

            DB::table('user_plans')->insert([
                'user_id' => $referrerProfile->user_id,
                'plan_id' => 1,
                'description' => 'Bonus Plan for referring ' . $this->user->username,

                'is_active' => true,
                'used_count' => 0,
                'plan_count' => $plan_count,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            // send push notification ot user
            (new SendPushNotification())->sendReferralBonusNotification($referrerProfile, $plan_count);
        }
    }


    private function formatResponse($gameSession, $questions): array
    {
        $gameInfo = new stdClass;
        $gameInfo->token = $gameSession->session_token;
        $gameInfo->startTime = $gameSession->start_time;
        $gameInfo->endTime = $gameSession->end_time;

        return [
            'questions' => $questions,
            'game' => $gameInfo
        ];
    }

}
