<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Game;
use App\Models\Question;
use App\Models\WalletTransaction;
use App\Models\Wallet;
use App\Models\Profile;
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

      if (!env('APP_DEBUG')) {
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

      //implement first game bonus here
      $this->_referralOnFirstGame();

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

                if (!$correctOption) {
                    $isCorrect = true;
                    Log::critical($question->id . ' has not correct answer');
                } else {
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

        //@TODO: remove hack.
        if ($game->duration > 60)
            $game->duration = 60;

        if ($game->is_winning) {
            $transaction = WalletTransaction::create([
                'wallet_id' => $this->user->wallet->id,
                'transaction_type' => 'CREDIT',
                'amount' =>  $game->amount_gained,
                'wallet_type' => 'CASH',
                'wallet_kind' => 'WINNINGS',
                'description' => 'Winnings',
                'reference' => Str::random(10)
            ]);
            $this->user->wallet->refresh();
        }

        return $this->sendResponse(
            [
                'game' => $game,
                'wallet' => $this->user->wallet,
                'rank' => $this->_rank()
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
        $results = DB::select(
            'SELECT g.*, p.avatar
            FROM (
                SELECT SUM(points_gained) AS score, username, user_id FROM games
                INNER JOIN users ON users.id = games.user_id
                GROUP BY username, user_id
                ORDER BY score DESC
                LIMIT 10
            ) g
            INNER JOIN profiles p ON g.user_id = p.user_id
            ORDER BY g.score DESC'
        );

        $mapResult = collect($results)->map(function($item){
            $avatar = $item->avatar;
            if( !is_null($avatar) && $avatar != ""){
               $item->avatar = asset('avatar/'.$avatar."?".rand());
            }
            return $item;
        });
        return $mapResult;
    }

    public function rank()
    {
        return $this->sendResponse($this->_rank(), 'User rank');
    }

    private function _rank()
    {
        $results = DB::select(
            'select SUM(points_gained) as score, user_id from games
            group by user_id
            order by score desc
            limit 100'
        );

        $user_index = 0;
        if (count($results) > 0) {
            $user_index = collect($results)->search(function ($user) {
                return $user->user_id == $this->user->id;
            });
        }

        if ($user_index === false)
            return 786;

        return $user_index + 1;
    }

  private function _referralOnFirstGame(){
    if($this->user->games->count()!=1){
      return;
    }

    if(config('trivia.bonus.enabled') && 
      config('trivia.bonus.signup.referral') && 
      config ('trivia.bonus.signup.referral_on_first_game') &&
      isset($this->user->referrer)
    ){
      $referrerId = Profile::where('referral_code', $this->user->referrer)->value('user_id');
        WalletTransaction::create([
          'wallet_id' => $referrerId,
          'transaction_type' => 'CREDIT',
          'amount' =>  config('trivia.bonus.signup.referral_amount'),
          'wallet_kind' => 'CREDITS',
          'description' => 'REFERRAL BONUS FOR '. $this->user->username,
          'reference' => Str::random(10)
      ]);
    }
  }

}