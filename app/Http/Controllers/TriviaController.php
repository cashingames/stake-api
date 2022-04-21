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
            'game_duration' => ['nullable'],
            'question_count'=>['nullable']
        ]);

        $category = Category::where('name', $data['category'])->first();
        $startTime = Carbon::createFromTimestamp($data['start_time'],'Africa/Lagos');
        $endTime = Carbon::createFromTimestamp($data['end_time'],'Africa/Lagos');

        $trivia = new Trivia;
        $trivia->name = $data['name'];
        $trivia->grand_price = $data['grand_price'];
        $trivia->point_eligibility = $data['point_eligibility'];
        $trivia->category_id = $category->id;
        $trivia->game_mode_id = 1;
        $trivia->game_type_id = 2;
        $trivia->start_time = $startTime;
        $trivia->end_time = $endTime;
        $trivia->save();
       
        if (isset($data['game_duration'])&&  !is_null($data['game_duration'])){
            $trivia->game_duration = $data['game_duration'];
            $trivia->save();
        };
        if (isset($data['question_count'])&&  !is_null($data['question_count'])){
            $trivia->question_count= $data['question_count'];
            $trivia->save();
        }


        $questions = $trivia->category->questions()
            ->whereNull('deleted_at')
            ->where('is_published', true)->inRandomOrder()->take($data['question_count'] + 10)->get();

        foreach ($questions as $q) {
            TriviaQuestion::create([
                'trivia_id' => $trivia->id,
                'question_id' => $q->id
            ]);
        }

        return $this->sendResponse(true, "Triva saved");
    }

    public function getTrivia()
    {
        // $trivia = Trivia::where('start_time', '<=', Carbon::now('Africa/Lagos'))
        //     ->where('end_time', '>', Carbon::now('Africa/Lagos'))
        //     ->get();
        $trivia = Trivia::limit(10)->orderBy('created_at','DESC')->get();
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
                ->where('is_published', true)->inRandomOrder()->take(20)->get();

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
