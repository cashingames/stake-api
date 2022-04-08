<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Trivia;
use App\Models\TriviaQuestion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TriviaController extends BaseController
{

    public function createTrivia(Request $request)
    {

        $data = $request->validate([
            'name' => ['required', 'string'],
            'category' => ['required', 'string'],
            'grand_price' => ['required', 'integer'],
            'point_eligibility' => ['required', 'integer'],
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
        ]);

        $category = Category::where('name', $data['category'])->first();

        Trivia::create([
            'name' => $data['name'],
            'grand_price' => $data['grand_price'],
            'point_eligibility' => $data['point_eligibility'],
            'category_id' => $category->id,
            'game_mode_id' => 1,
            'game_type_id' => 2,
            'start_time' => Carbon::parse($data['start_time'])->toDateTimeString(),
            'end_time' => Carbon::parse($data['end_time'])->toDateTimeString(),
        ]);

        return $this->sendResponse(true, "Triva saved");
    }

    public function getRunningTrivia()
    {
        $trivia = Trivia::where('start_time', '<=', Carbon::now())
            ->where('end_time', '>', Carbon::now())
            ->get();

        return $this->sendResponse($trivia, "Triva");
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

    public function saveTriviaQuestions(Request $request)
    {   
       
        $trivia = Trivia::find($request->triviaId);
        
        if ($trivia !== null) {
            
            $questions = $trivia->category->questions()
                ->whereNull('deleted_at')
                ->where('is_published', true)->inRandomOrder()->take(10)->get();
                
            foreach ($questions as $q) {
                TriviaQuestion::create([
                    'trivia_id' => $trivia->id,
                    'question_id' => $q->id
                ]);
            }
            return $this->sendResponse(true, "Triva questions saved");
        }


        return $this->sendResponse(true, "Triva questions saved");
    }
}
