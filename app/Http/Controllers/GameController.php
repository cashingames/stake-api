<?php

namespace App\Http\Controllers;
use App\Http\ResponseHelpers\GameSessionResponse;
use App\Http\ResponseHelpers\CommonDataResponse;
use App\Models\GameMode;
use App\Models\Boost;
use App\Models\Plan;
use App\Models\Category;
use App\Models\GameType;
use App\Models\UserBoost;
use App\Models\GameSessionQuestion;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\PlayGame\ReferralService;
use Illuminate\Support\Facades\Event;
use App\Events\AchievementBadgeEvent;
use App\Enums\AchievementType;
use App\Models\UserCategory;
use stdClass;

use function PHPUnit\Framework\isNull;

class GameController extends BaseController
{
    public function getCommonData(Request $request)
    {
        $result = new stdClass;

        $result->plans = Cache::rememberForever(
            'plans',
            fn () => Plan::where('is_free', false)->orderBy('price', 'ASC')->get()
        );

        $result->gameModes = Cache::rememberForever(
            'gameModes',
            fn () =>
            GameMode::select(
                'id',
                'name',
                'description',
                'icon',
                'background_color as bgColor',
                'display_name as displayName'
            )
                ->get()
        );

        $gameTypes = Cache::rememberForever('gameTypes', fn () => GameType::has('questions')->inRandomOrder()->get());

        // $categories = Cache::rememberForever('categories', fn () => Category::all());
        $categories = $this->showCategories();
        $gameInfo = DB::select("
        SELECT gt.name game_type_name, gt.id game_type_id, c.category_id category_id,
        c.id as subcategory_id, c.name subcategory_name, count(q.id) questons,
        (SELECT name from categories WHERE categories.id = c.category_id) category_name,
        (SELECT count(id) from game_sessions AS gs where gs.game_type_id = gt.id and
        gs.category_id = c.id and gs.user_id = {$this->user->id}) AS played
        FROM questions q
        JOIN categories_questions cq ON cq.question_id = q.id
        JOIN categories AS c ON c.id = cq.category_id
        JOIN game_types AS gt ON gt.id = q.game_type_id WHERE q.deleted_at IS NULL
        AND q.is_published = true AND c.is_enabled = true
        GROUP by cq.category_id, q.game_type_id HAVING count(q.id) > 0
        ");

        $gameInfo = collect($gameInfo);
        $toReturnTypes = [];
        foreach ($gameTypes as $type) {
            $uniqueCategories = $gameInfo->where('game_type_id', $type->id)->unique('category_id');
            $categoryIds = $uniqueCategories->values()->pluck('category_id');

            $_categories = $categories->filter(function ($x) use ($categoryIds) {
                return $categoryIds->contains($x->id);
            });

            $toReturnCategories = [];
            foreach ($_categories as $category) {



                $uSubs = $gameInfo->where('game_type_id', $type->id)->where('category_id', $category->id)->unique('subcategory_id');
                $_subcategories = $categories->filter(function ($x) use ($uSubs) {
                    return $uSubs->firstWhere('subcategory_id', $x->id) !== null;
                });

                $toReturnSubcategories = [];
                foreach ($_subcategories as $subcategory) {
                    $s = new stdClass;
                    $s->id = $subcategory->id;
                    $s->categoryId = $subcategory->category_id;
                    $s->name = $subcategory->name;
                    $s->icon = $subcategory->icon;
                    $toReturnSubcategories[] = $s;
                }

                $c = new stdClass;
                $c->id = $category->id;
                $c->name = $category->name;
                $c->description = $category->description;
                $c->icon = $category->icon;
                $c->bgColor = $category->background_color;
                $c->played = $gameInfo->where('game_type_id', $type->id)->where('category_id', $category->id)->sum('played');
                $c->subcategories = $toReturnSubcategories;
                $toReturnCategories[] = $c;
            }

            $_type = new stdClass;
            $_type->id = $type->id;
            $_type->name = $type->name;
            $_type->displayName = $type->display_name;
            $_type->description = $type->description;
            $_type->icon = $type->icon;
            $_type->bgColor = $type->background_color_2;

            $_type->categories = $toReturnCategories;


            $toReturnTypes[] = $_type;
        }

        $result->gameTypes = $toReturnTypes;
        $result->boosts = Boost::whereNull('deleted_at')
            ->where('name', '!=', 'Bomb')
            ->get();

        $result->minVersionCode = config('trivia.min_version_code_gameark');
        $result->minVersionForce = config('trivia.min_version_force_gameark');
        $result->boosts = Boost::all();

        $result->maximumExhibitionStakeAmount = config('trivia.maximum_exhibition_staking_amount');
        $result->minimumExhibitionStakeAmount = config('trivia.minimum_exhibition_staking_amount');
        $result->maximumChallengeStakeAmount = config('trivia.maximum_challenge_staking_amount');
        $result->minimumChallengeStakeAmount = config('trivia.minimum_challenge_staking_amount');
        $result->maximumLiveTriviaStakeAmount = config('trivia.maximum_live_trivia_staking_amount');
        $result->minimumLiveTriviaStakeAmount = config('trivia.minimum_live_trivia_staking_amount');
        $result->minimumWalletFundableAmount = config('trivia.wallet_funding.min_amount');
        $result->maximumWalletFundableAmount = config('trivia.wallet_funding.max_amount');
        $result->periodBeforeChallengeStakingExpiry =
            config('trivia.duration_hours_before_challenge_staking_expiry') . " hours";
        $result->totalWithdrawalAmountLimit = config('trivia.total_withdrawal_limit');
        $result->totalWithdrawalDays = config('trivia.total_withdrawal_days_limit');
        $result->hoursBeforeWithdrawal = config('trivia.hours_before_withdrawal');
        $result->minimumBoostScore = $this->MINIMUM_GAME_BOOST_SCORE;

        return $this->sendResponse((new CommonDataResponse())->transform($result), "Common data");
    }

    public function endSingleGame(Request $request, ReferralService $referralService)
    {

        Log::info($request->all());

        $game = $this->user->gameSessions()->where('session_token', $request->token)->sharedLock()->first();
        if ($game == null) {
            Log::info($this->user->username . " tries to end game with invalid token " . $request->token);
            return $this->sendError('Game Session does not exist', 'Game Session does not exist');
        }

        if ($game->state == "COMPLETED") {
            Log::info($this->user->username . " trying to end game a second time with " . $request->token);
            return $this->sendResponse($game, 'Game Ended');
        }

        $game->end_time = Carbon::now()->subSeconds(3); //this might be causing negative if the user submitted early
        $game->state = "COMPLETED";

        $points = 0;
        $wrongs = 0;

        //@TODO: Change our encryption method from base 64. It is not secure
        $questionsCount = 10;
        $chosenOptions = [];

        if (count($request->chosenOptions) > $questionsCount) {
            Log::error($this->user->username . " sent " . count($request->chosenOptions) . " answers as against $questionsCount for gamesession $request->token");

            //@TODO: we choose to pick first X options to avoid errors
            //refractor this to unique question id and pick 1 option for each
            $chosenOptions = array_slice($request->chosenOptions, 0, $questionsCount);
        } else {
            $chosenOptions = $request->chosenOptions;
        }

        DB::transaction(function () use ($chosenOptions, $game) {
            foreach ($chosenOptions as $value) {
                GameSessionQuestion::where('game_session_id', $game->id)
                    ->where('question_id', $value['question_id'])
                    ->update(['option_id' => $value['id']]);
            }
        });

        $questions = collect(Question::with('options')->whereIn('id', array_column($chosenOptions, 'question_id'))->get());

        foreach ($chosenOptions as $a) {
            $isCorect = $questions->firstWhere('id', $a['question_id'])->options->where('id', $a['id'])->where('is_correct', true)->first();

            if ($isCorect != null) {
                $points = $points + 1;
            } else {
                $wrongs = $wrongs + 1;
            }
        }

        $game->wrong_count = $wrongs;
        $game->correct_count = $points;

        $game = $this->processUserCoin($game);
        $game->points_gained = $points;
        $game->total_count = $points + $wrongs;

        $game->save();


        if ($points > 0) {
            $this->creditPoints($this->user->id, $game->points_gained, "Points gained from game played");
        }

        DB::transaction(function () use ($request, $game) {
            foreach ($request->consumedBoosts as $row) {
                $userBoost = UserBoost::where('user_id', $this->user->id)->where('boost_id', $row['boost']['id'])->first();

                $userBoost->update([
                    'used_count' => $userBoost->used_count + 1,
                    'boost_count' => $userBoost->boost_count - 1
                ]);

                DB::table('exhibition_boosts')->insert([
                    'game_session_id' => $game->id,
                    'boost_id' => $row['boost']['id'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        });

        // call for referral logic
        $referralService->gift();



        // call the event listener
        Event::dispatch(new AchievementBadgeEvent($request, AchievementType::GAME_PLAYED, $game));

        return $this->sendResponse((new GameSessionResponse())->transform($game), "Game Ended");
    }

    public function processUserCoin($game)
    {
        $coinsEarned = 0;
        $userScore = $game->correct_count;

        if ($userScore == config('trivia.coin_reward.user_scores.perfect_score')) {
            $coinsEarned =  config('trivia.coin_reward.coins_earned.perfect_coin');
        } else if ($userScore >= config('trivia.coin_reward.user_scores.high_score') && $userScore < config('trivia.coin_reward.user_scores.perfect_score')) {
            $coinsEarned = config('trivia.coin_reward.coins_earned.high_coin');
        } else if ($userScore >= config('trivia.coin_reward.user_scores.medium_score') && $userScore < config('trivia.coin_reward.user_scores.high_score')) {
            $coinsEarned = config('trivia.coin_reward.coins_earned.medium_coin');
        } else if ($userScore >=  config('trivia.coin_reward.user_scores.low_score') && $userScore < config('trivia.coin_reward.user_scores.medium_score')) {
            $coinsEarned = config('trivia.coin_reward.coins_earned.low_coin');
        } else {
            $coinsEarned = 0;
        }

        $game->coins_earned = $coinsEarned;
        $userCoin = $this->user->userCoins()->firstOrNew();
        $userCoin->coins_value = $userCoin->coins_value + $coinsEarned;
        $userCoin->save();

        $this->user->coinsTransaction()->create([
            'user_id' => $this->user->id,
            'transaction_type' => 'CREDIT',
            'description' => 'Game coins awarded',
            'value' => $coinsEarned
        ]);
        return $game;
    }

    private function showCategories()
    {
        $userCategories = $this->user->userCategories();
        if(count($userCategories) <= 0){
            return Cache::rememberForever('categories', fn () => Category::all()); 
        } else {
            return $userCategories;
        }
    }
}
