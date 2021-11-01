<?php

namespace App\Http\Controllers;

use App\Models\Mode;
use App\Models\User;
use App\Models\Boost;
use App\Models\Category;
use App\Models\GameType;
use App\Models\Challenge;
use App\Models\UserBoost;
use App\Models\CategoryRanking;
use App\Models\Achievement;
use App\Models\GameSession;
use App\Models\Question;
use App\Models\Notification;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\ChallengeInvite;
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
        $result->gameModes = Mode::select('id', 'name', 'display_name as displayName')->get();
        $gameTypes = GameType::inRandomOrder()->get();

        $categories = Category::all();

        $gameInfo = DB::select("
                        SELECT 
                            gt.name game_type_name, gt.id game_type_id, 
                            c.category_id category_id,
                                (select name from categories where categories.id = c.category_id) category_name,
                            c.id as subcategory_id, c.name subcategory_name, count(q.id) questons,
                            (select count(id) from game_sessions gs where gs.game_type_id = gt.id and gs.category_id = c.id and gs.user_id = {$this->user->id}) played
                        FROM questions q
                        JOIN categories c ON c.id = q.category_id
                        JOIN game_types gt ON gt.id = q.game_type_id
                        GROUP by q.category_id, q.game_type_id
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
                    $toReturnSubcategories[] = $s;
                }

                $c = new stdClass;
                $c->id = $category->id;
                $c->name = $category->name;
                $c->description = $category->description;
                $c->icon = $category->icon_name;
                $c->bgColor = $category->primary_color;
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
            $_type->bgColor = $type->primary_color_2;

            $_type->categories = $toReturnCategories;


            $toReturnTypes[] = $_type;
        }

        $result->gameTypes = $toReturnTypes;

        return $this->sendResponse($result, "");
    }

    public function claimAchievement($achievementId)
    {
        $achievement = Achievement::find($achievementId);
        if ($achievement === null) {
            return $this->sendError('Invalid Achievement', 'Invalid Achievement');
        }

        if ($this->user->points < $achievement->point_milestone) {
            return $this->sendError('You do not have enough points to claim this achievement', 'You do not have enough points to claim this achievement');
        }

        $isClaimed = DB::table('user_achievements')
            ->where('achievement_id', $achievement->id)
            ->where('user_id', $this->user->id)->first();

        if ($isClaimed === null) {

            $result = DB::table('user_achievements')->insert([
                'user_id' => $this->user->id,
                'achievement_id' => $achievement->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            return $this->sendResponse(
                $achievement,
                'Achievement Claimed'
            );
        } else {
            return $this->sendError(
                'You have already claimed this achievement',
                'You have already claimed this achievement'
            );
        }
    }

    public function startSingleGame(Request $request)
    {
        $category = Category::find($request->category);
        $type = GameType::find($request->type);
        $mode = Mode::find($request->mode);
        $questions = $category->questions()->take(10)->get()->shuffle();

        $gameSession = new GameSession();
        $gameSession->user_id = $this->user->id;
        $gameSession->mode_id = $mode->id;
        $gameSession->game_type_id = $type->id;
        $gameSession->category_id = $category->id;
        $gameSession->session_token = Str::random(40);
        $gameSession->start_time = Carbon::now();
        $gameSession->end_time = Carbon::now()->addMinutes(1);
        $gameSession->state = "ONGOING";
        $gameSession->save();

        $gameInfo = new stdClass;
        $gameInfo->token = $gameSession->session_token;
        $gameInfo->startTime = $gameSession->start_time;
        $gameInfo->endTime = $gameSession->end_time;

        $result = [
            'questions' => $questions,
            'game' => $gameInfo
        ];

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


    public function endSingleGame(Request $request)
    {

        // $request = json_decode($this->getSampleEndData());

        //get the session information
        $game = $this->user->gameSessions()->where('session_token', $request->token)->first();
        if (!$game) {
            return $this->sendError('Game Session does not exist', 'Game Session does not exist');
        }

        $game->end_time = Carbon::now()->subSeconds(1);
        $game->state = 'COMPLETED';

        $questions = Question::whereIn('id', array_column($request->chosenOptions, 'question_id'))->get();
        foreach ($request->chosenOptions as $a) {

            $isCorect = $questions->find($a['question_id'])
                ->options()
                ->where('id', $a['id'])
                ->where('is_correct', true)
                ->first();

            if ($isCorect) {
                $game->user_correct_count += 1;
            } else {
                $game->user_wrong_count += 1;
            }
        }

        $game->user_won = $game->user_correct_count > $game->user_wrong_count;
        $game->user_points_gained = $game->user_correct_count * 5; //@TODO to be revised

        $game->save();

        $this->creditPoints($this->user->id, $game->user_points_gained, "Points gained from correct game answers");
        $this->updateRanking($this->user->id, $game->category_id, $game->user_points_gained);

        foreach ($request->consumedBoosts as $row) {
            $userBoost = UserBoost::where('user_id', $this->user->id)->where('boost_id', $row['boost']['id'])->first();

            $userBoost->update([
                'used_count' => $userBoost->used_count + 1,
                'boost_count' => $userBoost->boost_count - 1
            ]);
        }

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

        if ($gameSession === null) {
            return $this->sendError('Game Session does not exist', 'Game Session does not exist');
        }
        //credit user with points
        $isChallenge = Mode::where("id", $gameSession->mode_id)->first();

        if (($isChallenge->name) !== "Challenge") {
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


        //update category rankings for user
        $this->updateRanking($this->user->id, $gameSession->category_id, $gameSession->user_points_gained);

        //update category rankings for opponent
        $this->updateRanking($gameSession->opponent_id, $gameSession->category_id, $gameSession->opponent_points_gained);

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


    private function updateRanking($userId, $catId, $points)
    {
        //category rankings
        //get category id of played subcategory
        $isCategory = Category::where('id', $catId)->select('category_id')->first();
        //if played category is not a subcategory
        if ($isCategory === null) {
            $ranking = CategoryRanking::where('user_id', $userId)->where('category_id', $catId)->first();

            if ($ranking === null) {
                CategoryRanking::create([
                    'user_id' => $userId,
                    'category_id' => $catId,
                    'points_gained' => $points
                ]);
                return;
            }
            $ranking->update(['points_gained' => $ranking->points_gained + $points]);
            return;
        }

        $ranking = CategoryRanking::where('user_id', $userId)->where('category_id', $isCategory->category_id)->first();

        if ($ranking === null) {
            CategoryRanking::create([
                'user_id' => $userId,
                'category_id' => $isCategory->category_id,
                'points_gained' => $points
            ]);
            return;
        }
        $ranking->update(['points_gained' => $ranking->points_gained + $points]);
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
