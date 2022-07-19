<?php

namespace App\Http\ResponseHelpers;

use App\Enums\ChallengeGameSessionStatus;
use \Illuminate\Http\JsonResponse;
use App\Traits\Utils\AvatarUtils;

class ChallengeLeaderboardResponse
{
    use AvatarUtils;

    public string $challengerUsername;
    public string $opponentUsername;
    public ChallengeGameSessionStatus $status;
    public int $challengerDuration;
    public int $opponentDuration;
    public int $challengerPoint;
    public int $opponentPoint;
    public $challengerAvatar;
    public $opponentAvatar;

    public function transform($data): JsonResponse
    {
        $presenter = new ChallengeLeaderboardResponse;
        $presenter->challengerUsername = $data->username;
        $presenter->opponentUsername = $data->opponentUsername;
        $presenter->challengerAvatar = $this->getAvatarUrl($data->avatar);
        $presenter->opponentAvatar = $this->getAvatarUrl($data->opponentAvatar);
        $presenter->challengerPoint = ($data->challengerPoint == NULL) ? 0 : $data->challengerPoint;
        $presenter->opponentPoint = ($data->opponentPoint == NULL) ? 0 : $data->opponentPoint;
        $presenter->challengerDuration = ($data->challengerFinishduration == NULL) ? 0 : $data->challengerFinishduration;
        $presenter->opponentDuration = ($data->opponentFinishduration == NULL) ? 0 : $data->opponentFinishduration;
        $presenter->status =  $this->getStatus($data->challengerStatus, $data->opponentStatus);
        return response()->json($presenter);
    }


    private function getStatus($challengerStatus, $opponentStatus): ChallengeGameSessionStatus
    {
        if ($challengerStatus == 'PENDING' && $opponentStatus == 'PENDING') {
            return ChallengeGameSessionStatus::Pending;
        } else if ($challengerStatus == NULL && $opponentStatus  == NULL) {
            return ChallengeGameSessionStatus::Pending;
        } 
        else if ($challengerStatus != 'COMPLETED' && $opponentStatus != 'COMPLETED') {
            return ChallengeGameSessionStatus::Ongoing;
        }
         else {
            return ChallengeGameSessionStatus::Closed;
        }

    }
}
