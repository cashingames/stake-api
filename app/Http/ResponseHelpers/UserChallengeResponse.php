<?php

namespace App\Http\ResponseHelpers;

use App\Enums\ChallengeStatus;
use App\Models\ChallengeGameSession;
use \Illuminate\Http\JsonResponse;


class UserChallengeResponse
{
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
            $presenter->date = $data->created_at;
            $response[] = $presenter;
        }

        return response()->json($response);
    }


    private function getStatus($challengeId): ChallengeStatus
    {
        $getChallengeGameSession = ChallengeGameSession::where('challenge_id', $challengeId);
        if ($getChallengeGameSession->count() <= 0) {
            return  ChallengeStatus::Pending;
        } else if ($getChallengeGameSession->where('state', 'COMPLETED')->count() >= 2) {
            return ChallengeStatus::Closed;
        } else {
            return ChallengeStatus::Ongoing;
        }
    }
}
