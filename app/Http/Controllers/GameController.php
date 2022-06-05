<?php

namespace App\Http\Controllers;

use App\Models\GameMode;
use App\Models\Boost;
use App\Models\Plan;
use App\Models\Category;
use App\Models\UserPlan;
use App\Models\GameType;
use App\Models\UserBoost;
use App\Models\Achievement;
use App\Models\GameSession;
use App\Models\Question;
use App\Models\Trivia;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TriviaQuestion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;

class GameController extends BaseController
{
    public function getCommonData()
    {
        $result = new stdClass;

        $result->achievements = Cache::rememberForever('achievements', fn () => Achievement::all());

        $result->boosts = Cache::rememberForever('boosts', fn () => Boost::all());

        $result->plans = Cache::rememberForever('plans', fn () => Plan::where('is_free', false)->orderBy('price', 'ASC')->get());

        $result->gameModes = Cache::rememberForever(
            'gameModes',
            fn () =>
            GameMode::select('id', 'name', 'description', 'icon', 'background_color as bgColor', 'display_name as displayName')->get()
        );

        $gameTypes = Cache::rememberForever('gameTypes', fn () => GameType::has('questions')->inRandomOrder()->get());

        $categories = Cache::rememberForever('categories', fn () => Category::all());


        $gameInfo = DB::select("
                        SELECT 
                            gt.name game_type_name, gt.id game_type_id, 
                            c.category_id category_id, c.id as subcategory_id, c.name subcategory_name, count(q.id) questons,
                            (SELECT name from categories WHERE categories.id = c.category_id) category_name,
                            (SELECT count(id) from game_sessions gs where gs.game_type_id = gt.id and gs.category_id = c.id and gs.user_id = {$this->user->id}) played
                        FROM questions q
                        JOIN categories c ON c.id = q.category_id
                        JOIN game_types gt ON gt.id = q.game_type_id 
                        WHERE q.deleted_at IS NULL AND q.is_published = true
                        GROUP by q.category_id, q.game_type_id
                        HAVING count(q.id) > 0
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
        $result->minVersionCode = config('trivia.min_version_code');
        $result->minVersionForce =  config('trivia.min_version_force');
        $result->hasLiveTrivia =  false;
        $result->upcomingTrivia =  null;
        $result->liveTrivia = Trivia::active()->first(); //@TODO: return playedStatus for users that have played and status 

        return $this->sendResponse($result, "Common data");
    }

    public function claimAchievement($achievementId)
    {
        $achievement = Achievement::find($achievementId);
        if ($achievement === null) {
            return $this->sendError('Invalid Achievement', 'Invalid Achievement');
        }
        $userPoints = $this->user->points();

        if ($userPoints < $achievement->point_milestone) {
            return $this->sendError('You do not have enough points to claim this achievement', 'You do not have enough points to claim this achievement');
        }

        $isClaimed = DB::table('user_achievements')
            ->where('achievement_id', $achievement->id)
            ->where('user_id', $this->user->id)->first();

        if ($isClaimed !== null) {

            return $this->sendError(
                'You have already claimed this achievement',
                'You have already claimed this achievement'
            );
        }

        Achievement::orderBy('point_milestone', 'ASC')->get()->map(function ($a) {

            $checkIsClaimed = DB::table('user_achievements')
                ->where('achievement_id', $a->id)
                ->where('user_id', $this->user->id)->first();

            if ($checkIsClaimed === null) {
                $userPoints = $this->user->points();
                if ($a->point_milestone <= $userPoints) {
                    DB::table('user_achievements')->insert([
                        'user_id' => $this->user->id,
                        'achievement_id' => $a->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }
            }
        });

        return $this->sendResponse(
            $achievement,
            'Achievement Claimed'
        );
    }

    public function startSingleGame(Request $request)
    {
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
        $gameSession->end_time = Carbon::now()->addMinutes(1);
        $gameSession->state = "ONGOING";

        $questions = [];

        if ($request->has('trivia')) {

            $fetchTriviaQuestions = TriviaQuestion::where('trivia_id', $request->trivia)->get();

            foreach ($fetchTriviaQuestions as $q) {
                $_question = Question::find($q->question_id); //@TODO: Improve performance bottleneck
                if ($_question !== null) {
                    $questions[] = $_question;
                }
            }
            $gameSession->trivia_id = $request->trivia;
        } else {

            $plan = $this->user->getNextFreePlan() ?? $this->user->getNextPaidPlan();
            if ($plan == null) {
                return $this->sendResponse('No available games', 'No available games');
            }

            $query = $category
                ->questions()
                ->where('is_published', true);

            if ($plan->is_free) {
                $query->whereLevel('easy');
            }

            $questions = $query->inRandomOrder()->take(20)->get()->shuffle();

            $userPlan = UserPlan::where('id', $plan->pivot->id)->first();
            $userPlan->update(['used_count' => $userPlan->used_count + 1]);

            if ($plan->game_count * $userPlan->plan_count <= $userPlan->used_count) {
                $userPlan->update(['is_active' => false]);
            }

            $gameSession->plan_id = $plan->id;
        }

        $gameSession->save();

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
        if ($this->user->withCount('gameSessions')->first()->game_sessions_count > 1) {
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

            DB::table('user_plans')->insert([
                'user_id' => $referrerProfile->user_id,
                'plan_id' => 1,
                'is_active' => true,
                'used_count' => 0,
                'plan_count' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }

    public function endSingleGame(Request $request)
    {
        Log::info($request->all());

        $game = $this->user->gameSessions()->where('session_token', $request->token)->first();
        if ($game === null) {
            return $this->sendError('Game Session does not exist', 'Game Session does not exist');
        }
        //@TODO Remove after fixing double submission bug.
        if ($game->state === "COMPLETED") {
            return $this->sendResponse($game, 'Game Ended');
        }

        $game->end_time = Carbon::now()->subSeconds(1);
        $game->state = 'COMPLETED';

        $questions = collect(Question::with('options')->whereIn('id', array_column($request->chosenOptions, 'question_id'))->get());
        $points = 0;
        $wrongs = 0;
        foreach ($request->chosenOptions as $a) {

            $isCorect = $questions->firstWhere('id', $a['question_id'])->options->where('id', $a['id'])->where('is_correct', base64_encode(true))->first();

            if ($isCorect != null) {
                $points = $points + 1;
            } else {
                $wrongs = $wrongs + 1;
            }
        }

        $game->wrong_count = $wrongs;
        $game->correct_count = $points;
        $game->points_gained = $points * 5; //@TODO to be revised
        $game->total_count = $points + $wrongs;

        $game->save();

        if ($points > 0) {
            $this->creditPoints($this->user->id, $game->points_gained, "Points gained from game played");
        }


        foreach ($request->consumedBoosts as $row) {
            $userBoost = UserBoost::where('user_id', $this->user->id)->where('boost_id', $row['boost']['id'])->first();

            $userBoost->update([
                'used_count' => $userBoost->used_count + 1,
                'boost_count' => $userBoost->boost_count - 1
            ]);
        }

        //find if this is the first time this user is playing this subcategory
        // if (GameSession::where('category_id')->first() == null) {
        //     $this->creditPoints($this->user->id, 30, "Bonus for playing new category");
        // }

        return $this->sendResponse($game, 'Game Ended');
    }
}
