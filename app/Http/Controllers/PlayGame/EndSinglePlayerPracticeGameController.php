<?php

namespace App\Http\Controllers\PlayGame;

use App\Http\Controllers\Controller;
use App\Http\ResponseHelpers\ResponseHelper;
use App\Models\ChallengeRequest;
use App\Models\Question;
use App\Models\StakingOdd;
use Illuminate\Http\Request;
use stdClass;

class EndSinglePlayerPracticeGameController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'chosenOptions' => ['required', 'array'],
        ]);

        $singlePlayerRequest = ChallengeRequest::where('challenge_request_id', $data['token'])->first();
     
        $questions = collect(Question::with('options')->whereIn('id', array_column($data['chosenOptions'], 'question_id'))->get());

        $points = 0;
        $wrongs = 0;
        foreach ($data['chosenOptions'] as $a) {
            $isCorect = $questions->firstWhere('id', $a['question_id'])->options->where('id', $a['id'])->where('is_correct', true)->first();

            if ($isCorect != null) {
                $points = $points + 1;
            } else {
                $wrongs = $wrongs + 1;
            }
        }

        $stakingOdd = StakingOdd::where('score', $points)->active()->first()->odd ?? 1;
        $amountWon = $stakingOdd * $singlePlayerRequest->amount;
       
        $singlePlayerRequest->update([
            'status' => 'COMPLETED',
            'score' => $points,
            'amount_won' => $amountWon,
            'ended_at' => now()
        ]);
        $singlePlayerRequest->refresh();
        
        dd($amountWon . " ". $singlePlayerRequest->status );
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
