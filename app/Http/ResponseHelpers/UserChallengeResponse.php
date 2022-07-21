<?php

namespace App\Http\ResponseHelpers;

use App\Enums\ChallengeStatus;
use App\Models\Challenge;
use App\Models\ChallengeGameSession;
use App\Traits\Utils\DateUtils;
use Hamcrest\Core\IsNull;
use \Illuminate\Http\JsonResponse;


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
            $response[] = $presenter;
        }

        return response()->json($response);
    }


    private function getStatus($challengeId): ChallengeStatus
    {
        $getChallengeGameSession = ChallengeGameSession::where('challenge_id', $challengeId);
        $declinedChallenge = Challenge::where('id', $challengeId)->where('status', 'DECLINED')->first();
        if (!is_null($declinedChallenge)) {
            return ChallengeStatus::Declined;
        } else if ($getChallengeGameSession->count() <= 0 && is_null($declinedChallenge)) {
            return  ChallengeStatus::Pending;
        } else if ($getChallengeGameSession->where('state', 'COMPLETED')->count() >= 2) {
            return ChallengeStatus::Closed;
        } else {
            return ChallengeStatus::Ongoing;
        }
    }
}
