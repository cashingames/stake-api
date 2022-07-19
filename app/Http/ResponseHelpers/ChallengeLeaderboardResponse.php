<?php

namespace App\Http\ResponseHelpers;

use App\Enums\ChallengeStatus;
use \Illuminate\Http\JsonResponse;
use App\Traits\Utils\AvatarUtils;

class ChallengeLeaderboardResponse
{
    use AvatarUtils;

    public string $challengerUsername;
    public string $opponentUsername;
    public string $status;
    public ChallengeStatus $challengeStatus;
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
        $presenter->challengerStatus = ($data->challengerStatus == NULL) ? 'PENDING' : $data->challengerStatus;
        $presenter->opponentStatus = ($data->opponentStatus == null) ? 'PENDING' :  $data->opponentStatus;
        $presenter->challengeStatus = $this->getStatus($data->challengerStatus, $data->opponentStatus);
        return response()->json($presenter);
    }


    private function getStatus($challengerStatus, $opponentStatus): ChallengeStatus
    {
        if ($challengerStatus == 'PENDING' && $opponentStatus == 'PENDING') {
            return ChallengeStatus::Pending;
        } else if ($challengerStatus == NULL && $opponentStatus  == NULL) {
            return ChallengeStatus::Pending;
        } 
        else if ($challengerStatus != 'COMPLETED' && $opponentStatus != 'COMPLETED') {
            return ChallengeStatus::Ongoing;
        }
         else {
            return ChallengeStatus::Closed;
        }

    }
}
