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
use Illuminate\Support\Facades\Mail;
use stdClass;

class GameController extends BaseController
{

    public function modes()
    {
        return $this->sendResponse(Mode::all(), "Game Modes");
    }

    public function getCommonData()
    {
        $result = new stdClass;
        $result->achievements = Achievement::all();
        $result->boosts = Boost::all();
        $result->gameModes = Mode::select('id', 'name')->get();
        $result->gameTypes = GameType::inRandomOrder()
            ->select('name', 'description', 'icon', 'primary_color_2 as bgColor')
            ->get()->map(function ($item) {
                $item->isEnabled = $item->is_available;
                return $item;
            });
        $result->categories = DB::select(
            "select c.id, c.name, c.description, c.icon_name as icon, primary_color as bgColor,
                (select count(g.id) 
                    from game_sessions g 
                    where g.category_id IN 
                        (select id from categories 
                            where categories.category_id = c.id)) as played 
            from categories c  
            where c.category_id = 0
            "
        );
        $result->subcategories = DB::select("select c.id, c.name, c.category_id as categoryId from categories c where c.category_id != 0");

        //get no of subcategories
        // (select count(id) 
        //     from categories 
        //     where category_id=c.id) as subcategories, 

        return $this->sendResponse($result, "");
    }

    public function gameTypes()
    {
        $types = GameType::all();

        //sort based on availbility
        $sorted = $types->sortByDesc('is_available');

        return $this->sendResponse($sorted->values()->all(), "Game Types");
    }

    public function shuffleGameTypes()
    {
        $types = GameType::inRandomOrder()
            ->limit(3)
            ->get();
        return $this->sendResponse($types, "Random Game Types");
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
        if (!($request->has('subCatId')) || !($request->has('gameTypeId')) || !($request->has('modeId'))) {
            return $this->sendError('SubcategoryId, GametypeId and ModeId is required', 'SubcategoryId, GametypeId and ModeId is required');
        }

        $subCat = Category::find($request->subCatId);
        $gameType = GameType::find($request->gameTypeId);
        $mode = Mode::find($request->modeId);

        if (($subCat || $gameType || $mode) === null) {
            return $this->sendResponse('Invalid subcategory, game type or mode.', 'Invalid subcategory, game type or mode.');
        }

        $easyQuestions = $subCat->questions()->where('level', 'easy')->where('game_type_id', $gameType->id)->inRandomOrder()->take(config('trivia.game.questions_count') / 3);
        $mediumQuestions =  $subCat->questions()->where('level', 'medium')->where('game_type_id', $gameType->id)->inRandomOrder()->take(config('trivia.game.questions_count') / 3);
        $hardQuestions = $subCat->questions()->where('level', 'hard')->where('game_type_id', $gameType->id)->inRandomOrder()->take(config('trivia.game.questions_count') / 3);

        $questions = $hardQuestions->union($mediumQuestions)->union($easyQuestions)->get()->shuffle();

        $gameSession = new GameSession();

        $gameSession->user_id = $this->user->id;
        $gameSession->mode_id = $mode->id;
        $gameSession->game_type_id = $gameType->id;
        $gameSession->category_id = $subCat->id;
        $gameSession->session_token = Str::random(40);
        $gameSession->start_time = Carbon::now();
        $gameSession->end_time = Carbon::now()->addMinutes(1);
        $gameSession->state = 'ONGOING';
        $gameSession->save();

        $result = [
            'questions' => $questions,
            'game' => $gameSession
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

    public function endSingleGame(Request $request)
    {

        $request->validate([
            'sessionToken' => ['required', 'string'],
            'userPointsGained' => ['required', 'string'],
            'userWrongCount' => ['required', 'string'],
            'userCorrectCount' => ['required', 'string'],
        ]);
        $gameSession = GameSession::where("session_token", $request->sessionToken)->first();

        if ($gameSession === null) {
            return $this->sendError('Game Session does not exist', 'Game Session does not exist');
        }

        //credit points to user
        $this->creditPoints($this->user->id, $request->userPointsGained, "Points gained from correct game answers");

        $gameSession->state = "COMPLETED";
        $gameSession->user_points_gained = $request->userPointsGained;
        $gameSession->user_wrong_count = $request->userWrongCount;
        $gameSession->user_correct_count = $request->userCorrectCount;
        if ($request->userCorrectCount > $request->userWrongCount) {
            $gameSession->user_won = true;
        }

        $gameSession->save();

        $this->updateRanking($this->user->id, $gameSession->category_id, $gameSession->user_points_gained);

        return $this->sendResponse($gameSession, 'Game Ended');
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
            //User has finished boost , reset used count
            $userBoost->used_count = 0;
            $userBoost->save();
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
            'opponentEmail' => ['required', 'email'],
            'categoryId' => ['required'],
            'gameTypeId' => ['required'],
        ]);

        $opponent = User::where('email', $request->opponentEmail)->first();

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

    public function recentlyPlayed()
    {
        $recentGames = [];
        $games = GameSession::where('user_id', $this->user->id)->latest()->limit(3)->get();
        foreach ($games as $g) {
            $category = Category::where('id', $g->category_id)->first();
            $recentGames[] = $category;
        }
        return $this->sendResponse($recentGames, 'Recent Games');
    }
}
