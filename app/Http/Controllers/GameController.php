<?php

namespace App\Http\Controllers;

use App\Category;
use App\Game;
use App\Question;
use App\WalletTransaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GameController extends BaseController
{
    /**
     *
     */
    public function start(Request $request)
    {
        if (!$request->liveId) {
            return $this->sendError([
                'live' => 'Please select a live to play'
            ], "Failed game attempt");
        }

        //get the user information
        $plan = $this->user->activePlans()->wherePivot('id', $request->liveId)->first();
        if (!$plan) {
            return $this->sendError([
                'live' => 'Invalid live supplied'
            ], "Failed game attempt");
        }

        $category = Category::find($request->categoryId);
        if (!$category) {
            return $this->sendError([
                'category' => 'Invalid category supplied'
            ], "Failed game attempt");
        }

        if ($plan->pivot->used >= $plan->games_count) {
            return $this->sendError(
                ['plan' => 'This plan has been exhaused'],
                'Game cannot start'
            );
        }

        if(!env('APP_DEBUG')){
          DB::table('user_plan')
              ->where('id', $plan->pivot->id)
              ->update(
                  [
                      'used' => $plan->pivot->used + 1,
                      'is_active' => ($plan->pivot->used + 1) < $plan->games_count
                  ]
              );
        }

        $game = new Game();
        $game->user_id = $this->user->id;
        $game->plan_id = $plan->id;
        $game->live_id = $plan->pivot->id;
        $game->category_id = $category->id;
        $game->session_token = Str::random(40);
        $game->start_time = Carbon::now();
        $game->expected_end_time = Carbon::now()->addMinutes(1);
        $game->state = 'ONGOING';
        $game->total_count = 10;
        $game->save();

        $result = [
            'game' => $game
        ];

        if ($request->loadQuestions) {
            $easyQuestions = $category->questions()->where('level', 'easy')->inRandomOrder()->take(5);
            $mediumQuestions =  $category->questions()->where('level', 'medium')->inRandomOrder()->take(5);
            $hardQuestions = $category->questions()->where('level', 'hard')->inRandomOrder()->take(5);

            $questions = $hardQuestions->union($mediumQuestions)->union($easyQuestions)->get()->shuffle();

            $result['questions'] = $questions;
        }

        return $this->sendResponse($result, "Game started");
    }


    /**
     *
     */
    public function fetchQuestion(Request $request, String $sessionToken)
    {

        $level = 'easy';
        $nextLevel = '';
        $correctConsecutiveCount = 0;

        $game = $this->user->games()->where('session_token', $sessionToken)->first();
        if (!$game) {
            return $this->sendError(['session_token' => 'Game session token does not exist'], "No ongoing game");
        }

        $seenGameQuestions = $game->questions()->without('options')->orderBy('game_questions.created_at', 'desc')->get();

        $previousQuestion = $seenGameQuestions->first();
        if ($previousQuestion) {
            $level = $previousQuestion->level;
        }

        //get last two questions
        $lastTwoConsecutiveQuestions = $seenGameQuestions->take(2);

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

        $question = $game->category->questions()
            ->where('level', $level)
            ->whereNotIn('id', $seenGameQuestions->pluck('id'))
            ->inRandomOrder()
            ->first();

        return $this->sendResponse($question, "Question fetched");
    }

    /**
     *
     */
    public function saveQuestionResponse(Request $request, String $sessionToken)
    {
        $question = Question::find($request->questionId);
        $correctOption = $question->options()->where('is_correct', 1)->first();
        $isCorrect = $correctOption->id == $request->optionId;

        $game = $this->user->games()->where('session_token', $sessionToken)->first();
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

    /**
     *
     */
    public function fetchSubmitQuestion(Request $request, String $sessionToken)
    {

        try {
            $this->saveQuestionResponse($request, $sessionToken);
        } catch (Exception $ex) {
        }

        return $this->fetchQuestion($request, $sessionToken);
    }


    /**'
     *
     */
    public function end(Request $request, String $sessionToken)
    {
        //get the session information
        $game = $this->user->games()->where('session_token', $sessionToken)->first();
        $game->end_time = Carbon::now()->subSeconds(1);
        $game->duration = Carbon::parse($game->start_time)->diffInSeconds(Carbon::parse($game->end_time));
        $game->state = 'COMPLETED';

        if ($request->answers) {

            $questions = Question::whereIn('id', array_column($request->answers, 'questionId'))->get();
            foreach ($request->answers as $a) {

                if (!$a || !$a['questionId']) {
                    continue;
                }

                $question = $questions->find($a['questionId']);
                $correctOption = $question->options->where('is_correct', 1)->first();
                
                if(!$correctOption){
                  $isCorrect = true;
                  Log::critical($question->id.' has not correct answer');
                }else{
                  $isCorrect = $correctOption->id == $a['optionId'];
                }


                if ($isCorrect) {
                    $game->correct_count += 1;
                } else {
                    $game->wrong_count += 1;
                }

                $game->questions()->save(
                    $question,
                    [
                        'question_id' => $question->id, 'is_correct' => $isCorrect, 'option_id' => $a['optionId'],
                        'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
                    ]
                );
            }
        }

        $game->setWinnings();
        $game->save();

        //@TODO: remove hack
        if ($game->duration > 60)
            $game->duration = 60;

        if ($game->is_winning) {
            $transaction = WalletTransaction::create([
                'wallet_id' => $this->user->wallet->id,
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

        $results = DB::select(
            'select SUM(points_gained) as score, username from games
            inner join users on users.id = games.user_id
            where games.created_at between ? and ?
            group by username
            order by score desc
            limit 10 ',
            [$firstDayTimeThisWeek, $firstDayTimeNextWeek]
        );

        return $results;
    }
}
