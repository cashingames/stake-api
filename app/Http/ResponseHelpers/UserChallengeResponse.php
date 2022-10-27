<?php

namespace App\Http\ResponseHelpers;

use App\Enums\ChallengeStatus;
use App\Models\Challenge;
use App\Models\ChallengeGameSession;
use App\Traits\Utils\DateUtils;
use Hamcrest\Core\IsNull;
use \Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class UserChallengeResponse
{
    use DateUtils;

    public string $playerUsername;
    public string $opponentUsername;
    public  $date;
    public string $subcategory;
    public int $challengeId;
    public ChallengeStatus $status;


    public function transform($challenges): JsonResponse
    {
        $response = [];
        foreach ($challenges as $data) {

            $presenter = new UserChallengeResponse;
            $presenter->subcategory = $data->name;
            $presenter->playerUsername = $data->username;
            $presenter->opponentUsername = $data->opponentUsername;
            $presenter->status = $this->getStatus($data->id);
            $presenter->challengeId = $data->id;
            $presenter->date = $this->toNigeriaTimeZoneFromUtc($data->created_at)->toDateTimeString();
            $presenter->flag = $this->getParticipantFlag($data->id);
            $response[] = $presenter;
        }

        return response()->json($response);
    }

    // get flag based on current user
    private function getParticipantFlag($challengeId)
    {
        $sessions = ChallengeGameSession::where('challenge_id', $challengeId)->limit(2)->get();
        if (count($sessions) !== 2) {
            return false;
        }
        $user = auth()->user();

        $currentUserScore = $sessions->firstWhere('user_id', $user->id)->points_gained; //('points_gained')->all();
        $opponentUserScore = $sessions->firstWhere('user_id', '!=', $user->id)->points_gained; //->get('points_gained')->all();


        if ($currentUserScore > $opponentUserScore) {
            return "WON";
        }
        if ($currentUserScore < $opponentUserScore) {
            return "LOST";
        }
        return "DRAW";
    }
    private function getStatus($challengeId): ChallengeStatus
    {
        $challenge = Challenge::find($challengeId);
        $getChallengeGameSession = ChallengeGameSession::where('challenge_id', $challengeId);
        $declinedChallenge = Challenge::where('id', $challengeId)->where('status', 'DECLINED')->first();
        if (!is_null($declinedChallenge)) {
            return ChallengeStatus::Declined;
        } else if ($getChallengeGameSession->count() <= 0 && is_null($declinedChallenge)) {
            if (!is_null($challenge) && $challenge->created_at <= Carbon::now()->subHours(config('trivia.duration_hours_before_challenge_staking_expiry'))) {
                return ChallengeStatus::Expired;
            }
            return  ChallengeStatus::Pending;
        } else if ($getChallengeGameSession->where('state', 'COMPLETED')->count() >= 2) {
            return ChallengeStatus::Closed;
        } else {
            if (!is_null($challenge) && $challenge->created_at <= Carbon::now()->subHours(config('trivia.duration_hours_before_challenge_staking_expiry'))) {
                return ChallengeStatus::Expired;
            }
            return ChallengeStatus::Ongoing;
        }
    }
}
