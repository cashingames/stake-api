<?php

namespace App\Http\Controllers;

use App\Models\GameMode;
use App\Models\User;
use App\Models\Boost;
use App\Models\Plan;
use App\Models\Category;
use App\Models\UserPoint;
use App\Models\UserPlan;
use App\Models\GameType;
use App\Models\Challenge;
use App\Models\UserBoost;
use App\Models\CategoryRanking;
use App\Models\Achievement;
use App\Models\GameSession;
use App\Models\Question;
use App\Models\Trivia;
use App\Models\Notification;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\ChallengeInvite;
use App\Models\TriviaQuestion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use stdClass;

class GameController extends BaseController
{
    public function getCommonData()
    {
        $result = new stdClass;
        $result->achievements = Achievement::all();
        $result->boosts = Boost::all();
        $result->plans = Plan::where('is_free', false)->get();

        $result->gameModes = GameMode::select('id', 'name', 'description', 'icon', 'background_color as bgColor', 'display_name as displayName')->get();
        $gameTypes = GameType::has('questions')->inRandomOrder()->get();

        $categories = Category::all();

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
        $result->hasLiveTrivia = $this->getTriviaState();

        return $this->sendResponse($result, "");
    }

    private function getTriviaState(){
        $trivia = Trivia::where('start_time', '<=', Carbon::now('Africa/Lagos'))
        ->where('end_time', '>', Carbon::now('Africa/Lagos'))
        ->get()->count();

        if($trivia > 0){
            return true;
        }
        return false;
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
        $category = Category::find($request->category);
        $type = GameType::find($request->type);
        $mode = GameMode::find($request->mode);

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
            $questions = $category->questions()
                ->whereNull('deleted_at')
                ->where('is_published', true)->inRandomOrder()->take(20)->get()->shuffle();

            $plan = $this->user->getNextFreePlan() ?? $this->user->getNextPaidPlan();
            if ($plan == null) {
                return $this->sendResponse('No available games', 'No available games');
            } else {
                $userPlan = UserPlan::where('id', $plan->pivot->id)->first();
                $userPlan->update(['used_count' => $userPlan->used_count + 1]);

                if ($plan->game_count * $userPlan->plan_count <= $userPlan->used_count) {
                    $userPlan->update(['is_active' => false]);
                }
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

    public function startChallenge(Request $request)
    {
        if ($request->modeId == 2 && !($request->has('challengeId'))) {
            return $this->sendError('Challenge Mode requires the Challenge Id', 'Challenge Mode requires the Challenge Id');
        }
        if ($request->modeId == 2 && !($request->has('opponentId'))) {
            return $this->sendError('Challenge Mode requires an Opponent', 'Challenge Mode requires an Opponent');
        }

        if ($request->has('challengeId') && $request->has('opponentId')) {
            $challenge = Challenge::find($request->challengeId);

            if ($challenge === null) {
                return $this->sendError(
                    'Opponent is yet to accept the challenge',
                    'Opponent is yet to accept the challenge.
                You will be notified when your challenge request is accepted'
                );
            }
            $opponent = User::find($request->opponentId);

            if (($opponent === null)) {
                return $this->sendError(
                    'Invalid Opponent',
                    'Invalid Opponent'
                );
            }
            $subCat = Category::find($request->subCatId);
            $gameType = GameType::find($request->gameTypeId);
            $mode = Mode::find($request->modeId);

            if (($subCat || $gameType || $mode) === null) {
                return $this->sendResponse('Invalid subcategory, game type or mode.', 'Invalid subcategory, game type or mode.');
            }

            $isSelected = Question::where('challenge_id', $challenge->id)->get();

            if (count($isSelected) == 0) {
                $easyQuestions = $subCat->questions()->where('level', 'easy')->where('game_type_id', $gameType->id)->inRandomOrder()->take(config('trivia.game.questions_count') / 3);
                $mediumQuestions =  $subCat->questions()->where('level', 'medium')->where('game_type_id', $gameType->id)->inRandomOrder()->take(config('trivia.game.questions_count') / 3);
                $hardQuestions = $subCat->questions()->where('level', 'hard')->where('game_type_id', $gameType->id)->inRandomOrder()->take(config('trivia.game.questions_count') / 3);

                $selectedQuestions = $hardQuestions->union($mediumQuestions)->union($easyQuestions)->get()->shuffle();

                //tag questions to challenge
                foreach ($selectedQuestions as $q) {
                    Question::where('id', $q->id)->update([
                        'challenge_id' => $challenge->id,
                    ]);
                }

                $gameSession = new GameSession();

                $gameSession->user_id = $this->user->id;
                $gameSession->mode_id = $mode->id;
                $gameSession->game_type_id = $gameType->id;
                $gameSession->category_id = $subCat->id;
                $gameSession->challenge_id = $request->challengeId;
                $gameSession->opponent_id = $opponent->id;
                $gameSession->session_token = Str::random(40);
                $gameSession->start_time = Carbon::now();
                $gameSession->end_time = Carbon::now()->addMinutes(1);
                $gameSession->state = 'ONGOING';
                $gameSession->save();

                $challenge->game_session_id = $gameSession->id;
                $challenge->save();

                $result = [
                    'questions' => $selectedQuestions,
                    'game' => $gameSession
                ];
                return $this->sendResponse($result, 'Game Started');
            }

            $gameSession = GameSession::where('id', $challenge->game_session_id)->first();
            $result = [
                'questions' => $isSelected,
                'game' => $gameSession
            ];

            return $this->sendResponse($result, 'Game Started');
        }
        return $this->sendError('This Challenge could not be started', 'This Challenge could not be started');
    }

    private function giftReferrerOnFirstGame()
    {
        if ($this->user->gameSessions->count() > 1) {
            return;
        }

        $referrerProfile = $this->user->profile->getReferrerProfile();
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

        // $request = json_decode($this->getSampleEndData());

        //get the session information

        Log::info($request->all());

        $game = $this->user->gameSessions()->where('session_token', $request->token)->first();
        if (!$game) {
            return $this->sendError('Game Session does not exist', 'Game Session does not exist');
        }

        $game->end_time = Carbon::now()->subSeconds(1);
        $game->state = 'COMPLETED';

        $questions = Question::whereIn('id', array_column($request->chosenOptions, 'question_id'))->get();
        $points = 0;
        $wrongs = 0;
        foreach ($request->chosenOptions as $a) {

            $isCorect = $questions->find($a['question_id'])
                ->options()
                ->where('id', $a['id'])
                ->where('is_correct', true)
                ->first();

            if ($isCorect != null) {
                $points = $points + 1;
            } else {
                $wrongs = $wrongs + 1;
            }
        }

        $game->wrong_count = $wrongs;
        $game->points_gained = $points * 5; //@TODO to be revised

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


    public function endChallengeGame(Request $request)
    {
        $request->validate([
            'sessionToken' => ['required', 'string'],
            'opponentId' => ['required', 'string'],
            'userPointsGained' => ['required', 'string'],
            'userWrongCount' => ['required', 'string'],
            'userCorrectCount' => ['required', 'string'],
            'opponentPointsGained' => ['required', 'string'],
            'opponentWrongCount' => ['required', 'string',],
            'opponentCorrectCount' => ['required', 'string'],
        ]);

        $gameSession = GameSession::where("session_token", $request->sessionToken)->first();

        if ($gameSession == null) {
            return $this->sendError('Game Session does not exist', 'Game Session does not exist');
        }
        //credit user with points
        $isChallenge = Mode::where("id", $gameSession->mode_id)->first();

        if (($isChallenge->name) != "Challenge") {
            return $this->sendError('This Game was not played in Challenge mode', 'This Game was not played in Challenge mode');
        }

        $this->creditPoints($this->user->id, $request->userPointsGained, "Points gained from correct game answers");
        //credit opponent with points
        $this->creditPoints($request->opponentId, $request->opponentPointsGained, "Points gained from correct game answers");

        //save  details
        $gameSession->state = "COMPLETED";
        $gameSession->user_points_gained = $request->userPointsGained;
        $gameSession->user_wrong_count = $request->userWrongCount;
        $gameSession->user_correct_count = $request->userCorrectCount;
        $gameSession->opponent_points_gained = $request->opponentPointsGained;
        $gameSession->opponent_wrong_count = $request->opponentWrongCount;
        $gameSession->opponent_correct_count = $request->opponentCorrectCount;

        if ($request->userPointsGained > $request->opponentPointsGained) {
            $gameSession->user_won = true;
        }
        if ($request->opponentPointsGained > $request->userPointsGained) {
            $gameSession->opponent_won = true;
        }
        $gameSession->save();

        return $this->sendResponse($gameSession, 'Game Ended');
    }

    public function consumeBoost($boostId)
    {
        $userBoost = UserBoost::where('user_id', $this->user->id)->where('boost_id', $boostId)->first();
        if ($userBoost === null) {
            return $this->sendError('You are not subscribed to this boost', 'You are not subscribed to this boost');
        }

        if ($userBoost->boost_count <= 0) {
            return $this->sendError('You have used up this boost', 'You have used up this boost');
        }

        $userBoost->update([
            'used_count' => $userBoost->used_count + 1,
            'boost_count' => $userBoost->boost_count - 1
        ]);

        return $this->sendResponse($userBoost, 'Boost consumed');
    }

    public function sendChallengeInvite(Request $request)
    {
        $request->validate([
            'opponentId' => ['required'],
            'categoryId' => ['required'],
            'gameTypeId' => ['required'],
        ]);

        $opponent = User::find($request->opponentId);

        if ($opponent === null) {
            return $this->sendError('The selected opponent does not exist', 'The selected opponent does not exist');
        }

        $challenge = Challenge::create([
            'user_id' => $this->user->id,
            'opponent_id' => $opponent->id,
            'category_id' => $request->categoryId,
            'game_type_id' => $request->gameTypeId,
            'status' => 'PENDING',
        ]);

        Mail::send(new ChallengeInvite($opponent, $challenge->id));
        return $this->sendResponse($challenge, 'Challenge Invite Sent! You will be notified when your opponent responds.');
    }

    public function acceptChallenge($challengeId)
    {

        $challenge = Challenge::find($challengeId);

        if ($challenge === null) {
            return $this->sendError('No challenge found', 'No Challenge found');
        }

        $challenge->update(["status" => 'ACCEPTED']);

        $opponent = User::where("id", $challenge->user_id)->first();
        $me = User::where("id", $challenge->opponent_id)->first();

        $result = [
            "challenge" => $challenge,
            "opponent" => $opponent
        ];

        //notify challenger of accepted challenge
        Notification::create([
            'user_id' => $challenge->user_id,
            'title' => 'CHALLENGE ACCEPTED',
            'message' => $me->username . ' has accepted your challenge. Start game here: ' . config("app.web_app_url") . '/duel/profile',
        ]);
        return $this->sendResponse($result, 'Challenge Accepted');
    }

    public function declineChallenge($challengeId)
    {

        $challenge = Challenge::find($challengeId);

        if ($challenge === null) {
            return $this->sendError('No challenge found', 'No Challenge found');
        }

        $challenge->update(["status" => "DECLINED"]);

        $me = User::where("id", $challenge->opponent_id)->first();

        //notify challenger of declined challenge
        Notification::create([
            'user_id' => $challenge->user_id,
            'title' => 'CHALLENGE DECLINED',
            'message' => 'Your challenge invite to ' . $me->username . ' was declined.'
        ]);

        return $this->sendResponse("Challenge Declined", 'Challenge Declined');
    }

    private function getSampleEndData()
    {
        return '{
   "started":true,
   "ended":false,
   "token":"1MGuGBwrzjnOVWGIYwvUpK8faHPPtFdriDPHXHQZ",
   "startTime":"2021-10-27T05:29:38.236304Z",
   "endTime":"2021-10-27T05:30:38.236366Z",
   "chosenOptions":[
      {
         "id":57,
         "question_id":"15",
         "title":"YXQ=",
         "is_correct":"MA==",
         "isSelected":true
      }
   ],
   "consumedBoosts":[
      {
         "boostId":"4",
         "questionId":15
      },
      {
         "boostId":"4",
         "questionId":15
      }
   ]
}';
    }
}
