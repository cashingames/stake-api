<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Trivia;
use App\Models\TriviaQuestion;
use Illuminate\Support\Facades\DB;

class TriviaController extends BaseController
{   
    public function getRunningTrivia()
    {

        return $this->sendResponse(Trivia::where('is_on', true)->get(), "Triva");
    }

    public function getTriviaData($triviaId)
    {
        //get trivia leaders

        $query = 'SELECT r.points, p.first_name , p.last_name, p.user_id
        FROM (
            SELECT SUM(points_gained) AS points, user_id, username FROM game_sessions gs
            INNER JOIN users ON users.id = gs.user_id WHERE gs.trivia_id = ? group by gs.user_id
                order by points desc 
                limit 25
            ) r
            join profiles p on p.user_id = r.user_id
            order by r.points desc';

        $leaders = DB::select($query, [$triviaId]);
        
        //get user position
        $userIndex = -1;
       
        if (count($leaders) > 0) {
            $userIndex = collect($leaders)->search(function ($user) {
                return $user->user_id == $this->user->id;
            });
        }

        if ($userIndex === false || $userIndex === -1) {
            $userIndex = 786;
        }

        $data = [
            'leaders' => $leaders,
            'position' => $userIndex + 1
        ];
        
        return $this->sendResponse($data, "Triva data");
    }

    public function saveTriviaQuestions($triviaId){
        $trivia = Trivia::find($triviaId);

        $questions = $trivia->category->questions()
            ->whereNull('deleted_at')
            ->where('is_published', true)->inRandomOrder()->take(10)->get();

        foreach($questions as $q){
            TriviaQuestion::create([
                'trivia_id' => $trivia->id,
                'question_id' => $q->id
            ]);
        }
        return $this->sendResponse(true, "Triva questions saved");
    }
}
