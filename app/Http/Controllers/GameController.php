<?php

namespace App\Http\Controllers;

use App\Category;
use App\Game;
use App\Question;
use App\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpParser\Node\Expr\Cast\String_;

class GameController extends BaseController
{
    //
    public function start(Request $request)
    {
        if(!$request->liveId){
            return $this->sendError([
                'plan' => 'Invalid live supplied'
            ], "Failed game attempt");
        }
        //get the user information
        $user = auth()->user();
        $plan = $user->activePlans()->wherePivot('id', $request->liveId)->first();
        $category = Category::find($request->categoryId);

        if ($plan->pivot->used >= $plan->games_count) {
            return $this->sendError(
                ['plan' => 'This plan has been exhaused'],
                'Game cannot start'
            );
        }

        DB::table('user_plan')
            ->where('id', $plan->pivot->id)
            ->update(
                [
                    'used' => $plan->pivot->used + 1,
                    'is_active' => ($plan->pivot->used + 1) < $plan->games_count
                ]
            );


        $game = new Game();
        $game->user_id = $user->id;
        $game->plan_id = $plan->id;
        $game->live_id = $plan->pivot->id;
        $game->category_id = $category->id;
        $game->session_token = Str::random(40);
        $game->start_time = Carbon::now();
        $game->expected_end_time = Carbon::now()->addMinutes(1);
        $game->state = 'ONGOING';
        $game->total_count = 10;
        $game->save();

        return $this->sendResponse($game, "Game started");
    }

    //
    public function fetchQuestion(String $sessionToken)
    {
        $level = 'easy';
        $nextLevel = '';
        $correctConsecutiveCount = 0;

        $game = auth()->user()->games()->where('session_token', $sessionToken)->first();
        if (!$game) {
            return $this->sendError(['session_token' => 'Game session token does not exist'], "No ongoing game");
        }

        $previousQuestion = $game->questions()->orderBy('game_questions.created_at', 'desc')->first();
        if ($previousQuestion) {
            $level = $previousQuestion->level;
        }

        //get last two questions
        $lastTwoConsecutiveQuestions = $game->questions()
            ->orderBy('game_questions.created_at', 'desc')
            ->take(2)
            ->get();

        foreach ($lastTwoConsecutiveQuestions as $question) {
            if ($question->level == $level && $question->pivot->is_correct == "1") {
                $correctConsecutiveCount += 1;
            }
        }

        if ($correctConsecutiveCount == 2 && $level != 'hard') {
            if ($level == 'easy') {
                $nextLevel = 'medium';
            } else if ($level == 'medium') {
                $nextLevel = 'hard';
            } else {
                $nextLevel = $level;
            }

            $level = $nextLevel;
        }

        $question = $game->category->questions()->where('level', $level)->inRandomOrder()->take(1)->first();

        //check if the user already saw this question for this session
        //if true, try again
        $exists = $game->questions()->find($question->id);
        while ($exists) {
            error_log("caught a repitition ". $question);
            $newQuestion = $game->category->questions()->where('level', $level)->inRandomOrder()->take(1)->first();
            $exists = $game->questions()->find($newQuestion->id);

            if (!$exists) {
                $question = $newQuestion;
                break;
            }
        }

        return $this->sendResponse($question, "Question fetched");
    }

    //
    public function saveQuestionResponse(Request $request, String $sessionToken)
    {
        $question = Question::find($request->questionId);
        $correctOption = $question->options()->where('is_correct', 1)->first();
        $isCorrect = $correctOption->id == $request->optionId;

        $game = auth()->user()->games()->where('session_token', $sessionToken)->first();
        if ($isCorrect) {
            $game->correct_count += 1;
            $game->setWinnings();
        } else {
            $game->wrong_count += 1;
        }


        $game->questions()->save($question, ['question_id' => $question->id, 'is_correct' => $isCorrect, 'option_id' => $request->optionId, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        $game->end_time = Carbon::now()->subSeconds(1);
        $game->duration = Carbon::parse($game->start_time)->diffInSeconds(Carbon::parse($game->end_time));
        $game->save();

        $this->sendResponse(true, 'Response saved');
    }

    //
    public function end(String $sessionToken)
    {
        //get the session information
        $user = auth()->user();
        $game = $user->games()->where('session_token', $sessionToken)->first();
        $game->end_time = Carbon::now()->subSeconds(1);
        $game->duration = Carbon::parse($game->start_time)->diffInSeconds(Carbon::parse($game->end_time));
        $game->state = 'COMPLETED';
        $game->setWinnings();
        $game->save();

        //@TODO: remove hack
        if ($game->duration > 60)
            $game->duration = 60;

        if ($game->is_winning) {
            $transaction = WalletTransaction::create([
                'wallet_id' => $user->wallet->id,
                'transaction_type' => 'CREDIT',
                'amount' =>  $game->amount_gained,
                'wallet_type' => 'CASH',
                'description' => 'Winnings',
                'reference' => Str::random(10)
            ]);
        }

        return $this->sendResponse(
            [
                'game' => $game
            ],
            'Game finished'
        );
    }

    public function leaders()
    {
        return $this->sendResponse($this->_leaders(), 'Leaderboard data');
    }

    private function _leaders()
    {
        $firstDayTimeThisWeek = date('Y-m-d H:i:s', strtotime("last sunday"));
        $firstDayTimeNextWeek = date('Y-m-d H:i:s', strtotime("next sunday"));

        $games = Game::with(['user:id,username'])
            ->selectRaw('user_id, MAX(correct_count) as score, MIN(duration) as duration')
            // ->whereBetween('created_at', [$firstDayTimeThisWeek, $firstDayTimeNextWeek])
            ->groupBy('user_id')
            ->orderBy('score', 'desc')
            ->take(10)
            ->get();
        return $games;
    }
}
