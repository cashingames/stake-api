<?php

namespace App\Http\Controllers\PlayGame;

use App\Http\Controllers\Controller;
use App\Http\ResponseHelpers\ResponseHelper;
use App\Models\Question;
use Illuminate\Http\Request;
use stdClass;

class EndSinglePlayerPracticeGameController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'chosenOptions' => ['required', 'array'],
        ]);

        $questions = collect(Question::with('options')->whereIn('id', array_column($data['chosenOptions'], 'question_id'))->get());

        $points = 0;
        $wrongs = 0;
        $amountWon = 0;
        foreach ($data['chosenOptions'] as $a) {
            $isCorect = $questions->firstWhere('id', $a['question_id'])->options->where('id', $a['id'])->where('is_correct', true)->first();

            if ($isCorect != null) {
                $points = $points + 1;
            } else {
                $wrongs = $wrongs + 1;
            }
        }

        if($points > 0){
            $amountWon = rand(100, 1000);
        }
        $result = $this->prepare($amountWon, $points , $wrongs);
        return ResponseHelper::success($result);
    }

    private function prepare($amountWon, $points, $wrongs): object
    {
        $gameInfo = new stdClass;
       
        $gameInfo->amount_won = $amountWon;
        $gameInfo->correct_count = $points;
        $gameInfo->wrong_count = $wrongs;
        $gameInfo->total_count = $wrongs + $points;
        $gameInfo->points_gained = $points;
      
        return $gameInfo;
    }
}
