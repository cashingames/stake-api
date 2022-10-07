<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Question;
use App\Models\UserBoost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ChallengeGameSession;
use App\Actions\SendPushNotification;
use App\Notifications\ChallengeCompletedNotification;
use App\Notifications\ChallengeStatusUpdateNotification;
use App\Services\ChallengeGameService;

class EndChallengeGameController extends  BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, SendPushNotification $sendPushNotification)
    {
        Log::info($request->all());

        $game = $this->user->challengegameSessions->where('session_token', $request->token)->first();
        if ($game === null) {
            return $this->sendError('Challenge Game Session does not exist', 'Challenge Game Session does not exist');
        }
        //@TODO Remove after fixing double submission bug.
        if ($game->state === "COMPLETED") {
            return $this->sendResponse($game, 'Challenge Game Ended');
        }
        $game->end_time = Carbon::now()->subMilliseconds(500);
        $game->state = 'COMPLETED';
        $points = 0;
        $wrongs = 0;

        $questionsCount =  10;
        $chosenOptions =  $request->chosenOptions;

        if (count($chosenOptions) > $questionsCount) {
            Log::info($this->user->username . " sent " . count($request->chosenOptions) . " answers as against $questionsCount for gamesession $request->token on Challenge");
            array_slice($chosenOptions, $questionsCount);
            //return $this->sendError('Chosen options more than expected', 'Chosen options more than expected');
        }
        $questions = collect(Question::with('options')->whereIn('id', array_column($chosenOptions, 'question_id'))->get());

        foreach ($chosenOptions as $a) {
            $isCorect = $questions->firstWhere('id', $a['question_id'])->options->where('id', $a['id'])->where('is_correct', base64_encode(true))->first();

            if ($isCorect != null) {
                $points = $points + 1;
            } else {
                $wrongs = $wrongs + 1;
            }
        }

        $game->wrong_count = $wrongs;
        $game->correct_count = $points;
        $game->points_gained = $points; // stop multiplying by 5 * 5; //@TODO to be revised
        $game->total_count = $points + $wrongs;
        $game->save();

        foreach ($request->consumedBoosts as $row) {
            $userBoost = UserBoost::where('user_id', $this->user->id)->where('boost_id', $row['boost']['id'])->first();

            $userBoost->update([
                'used_count' => $userBoost->used_count + 1,
                'boost_count' => $userBoost->boost_count - 1
            ]);
        }

        
        $sendPushNotification->sendChallengeCompletedNotification($this->user, $game->challenge);

        if ($this->user->id == $game->challenge->user_id) {
            $recipient = $game->challenge->opponent;
        } else {
            $recipient = $game->challenge->users;
        }
        $recipient->notify(new ChallengeCompletedNotification($game->challenge, $this->user));
        
        $challengeGameService = new ChallengeGameService();
        $challengeGameService->creditStakeWinner($game->challenge);
        
        return $this->sendResponse($game, 'Challenge Game Ended');
    }

    
}
