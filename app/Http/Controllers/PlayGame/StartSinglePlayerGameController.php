<?php

namespace App\Http\Controllers\PlayGame;

use App\Enums\FeatureFlags;
use App\Http\Requests\StartSinglePlayerRequest;
use App\Models\GameMode;
use App\Models\Category;
use App\Models\UserPlan;
use App\Models\GameType;
use App\Models\GameSession;
use App\Models\Question;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TriviaQuestion;
use App\Services\FeatureFlag;
use App\Services\Odds\QuestionsHardeningService;
use App\Services\OddsComputer;
use App\Services\StakingService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController;
use App\Enums\GameType as EnumsGameType;

use stdClass;

class StartSinglePlayerGameController extends BaseController
{

    public function __invoke(Request $request, StartSinglePlayerRequest $reqeuestModel, EnumsGameType $customType)
    {
        $validated = $reqeuestModel->validated();
        // $validatedRequest = (object) $validated;
        // $currentGameType = GameTypeFactory::detect($validated);

        $isStakingGame = $request->has('staking_amount');
        $isLiveTriviaGame = $request->has('trivia');

        $category = Cache::rememberForever("category_$request->category", fn () => Category::find($request->category));
        $type = Cache::rememberForever("gametype_$request->type", fn () => GameType::find($request->type));
        $mode = Cache::rememberForever("gamemode_$request->mode", fn () => GameMode::find($request->mode));

        $gameSession = new GameSession();
        $gameSession->user_id = $this->user->id;
        $gameSession->game_mode_id = $mode->id;
        $gameSession->game_type_id = $type->id;
        $gameSession->category_id = $category->id;
        $gameSession->session_token = Str::random(40);
        $gameSession->start_time = Carbon::now();

        //@TODO  //if it's live trivia add the actual seconds 
        $gameSession->end_time = Carbon::now()->addMinutes(1);
        $gameSession->state = "ONGOING";

        $questionHardener = new QuestionsHardeningService($this->user, $category);

        //@TODO Separate live trivia result odds from exhibition result odds
        if (FeatureFlag::isEnabled(FeatureFlags::ODDS)) {

            $oddMultiplierComputer = new OddsComputer();

            $average = $questionHardener->getAverageOfLastThreeGames($isLiveTriviaGame ? 'trivia' : null);
            $odd = $oddMultiplierComputer->compute($this->user, $average, $isLiveTriviaGame);

            $gameSession->odd_multiplier = $odd['oddsMultiplier'];
            $gameSession->odd_condition = $odd['oddsCondition'];
        }

        $questions = [];

        if ($isLiveTriviaGame) {

            //ensure that this user has not played this trivia
            if ($this->user->gameSessions()->where('trivia_id', $request->trivia)->exists()) {
                return $this->sendError(['You have already played this triva.'], "Attempt to play trivia twice");
            }

            $triviaList = TriviaQuestion::where('trivia_id', $request->trivia)->inRandomOrder()->pluck('question_id');
            $questions = Question::whereIn('id', $triviaList)->get();
            $gameSession->trivia_id = $request->trivia;
        } else {

            $questions = $questionHardener->determineQuestions($isStakingGame);

            if (count($questions) < 20) {
                return $this->sendError('Category not available for now, try again later', 'Category not available for now, try again later');
            }

            if (!$isStakingGame) {
                $plan = $this->user->getNextFreePlan() ?? $this->user->getNextPaidPlan();
                if ($plan == null) {
                    return $this->sendResponse('No available games', 'No available games');
                }

                $userPlan = UserPlan::where('id', $plan->pivot->id)->first();
                $userPlan->update(['used_count' => $userPlan->used_count + 1]);

                if ($plan->game_count * $userPlan->plan_count <= $userPlan->used_count) {
                    $userPlan->update(['is_active' => false]);
                }

                $gameSession->plan_id = $plan->id;
            }

        }

        $gameSession->save();

        if ($this->shouldApplyExhibitionStaking($isStakingGame)) {
            $stakingService = new StakingService($this->user, 'exhibition');

            $stakingId = $stakingService->stakeAmount($request->staking_amount);

            $stakingService->createExhibitionStaking($stakingId, $gameSession->id);
        }

        Log::info("About to log selected game questions for game session $gameSession->id and user $this->user");

        $data = [];

        foreach ($questions as $question) {
            $data[] = [
                'question_id' => $question->id,
                'game_session_id' => $gameSession->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('game_session_questions')->insert($data);

        Log::info("questions logged for game session $gameSession->id and user $this->user");

        $gameInfo = new stdClass;
        $gameInfo->token = $gameSession->session_token;
        $gameInfo->startTime = $gameSession->start_time;
        $gameInfo->endTime = $gameSession->end_time;

        $result = [
            'questions' => $questions,
            'game' => $gameInfo
        ];

        $this->giftReferrerOnFirstGame();

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

            DB::table('user_plans')->insert([
                'user_id' => $referrerProfile->user_id,
                'plan_id' => 1,
                'description' => 'Bonus Plan for referring ' . $this->user->username,

                'is_active' => true,
                'used_count' => 0,
                'plan_count' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }

    private function shouldApplyExhibitionStaking($isStaking)
    {
        if (!$isStaking){
            return false;
        }
        return FeatureFlag::isEnabled(FeatureFlags::EXHIBITION_GAME_STAKING) ||
                FeatureFlag::isEnabled(FeatureFlags::TRIVIA_GAME_STAKING);
    }
}
