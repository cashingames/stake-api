<?php

namespace App\Http\Controllers;

use App\Category;
use App\Game;
use App\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class GameController extends BaseController
{
    //
    public function start(Request $request)
    {
        //get the user information
        $user = auth()->user();
        $plan = $user->plans()->find($request->planId);
        $category = Category::find($request->categoryId);

        //@TODO - Check if the used filed is exhausted
        $user->plans()->updateExistingPivot($plan->id, ['used' => $plan->pivot_used+1]);

        $game = new Game();
        $game->user_id = $user->id;
        $game->plan_id = $plan->id;
        $game->category_id = $category->id;
        $game->session_token = Str::random(40);
        $game->start_time = Carbon::now();
        $game->expected_end_time = Carbon::now()->addMinutes(1);
        $game->state = 'ONGOING';
        $game->save();

        return $this->sendResponse($game, "Game started");
    }

    //
    public function fetchQuestion(String $sessionToken)
    {
        $game = auth()->user()->games()->where('session_token', $sessionToken)->first();
        $question = $game->category->questions()->where('level', 'easy')->inRandomOrder()->take(1)->first();
        return $this->sendResponse($question, "Question fetched");
    }

    //
    public function saveQuestionResponse(Request $request)
    {
        //get the session information
        //determine if response is correct
        //update game session table
        //return success
    }

    //
    public function end(String $sessionId)
    {
        //get the session information
        //check if won or loss
        //credit wallet with equivalent of points if won
        //return points won and amount won
    }

}
