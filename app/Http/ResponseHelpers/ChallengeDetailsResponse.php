<?php

namespace App\Http\ResponseHelpers;

use App\Enums\ChallengeStatus;
use App\Models\User;
use \Illuminate\Http\JsonResponse;
use App\Traits\Utils\AvatarUtils;


class ChallengeDetailsResponse
{

    use AvatarUtils;

    public int $challengeId;
    public int $playerId;
    public string $playerUsername;
    public $playerAvatar;
    public int $opponentId;
    public string $opponentUsername;
    public $opponentAvatar;
    public string $gameModeName;
    public int $gameModeId;
    public $status;


    public function transform($data): JsonResponse
    {
       
        $presenter = new ChallengeDetailsResponse;

        $presenter->challenegeId = $data->challengeDetails->id;
        $presenter->playerId = $data->challengeDetails->user_id;
        $presenter->playerUsername = $data->playerUsername;
        $presenter->playerAvatar = $this->getAvatarUrl($data->playerAvatar);
        $presenter->opponentId = $data->challengeDetails->opponent_id;
        $presenter->opponentUsername = $data->opponentUsername;
        $presenter->opponentAvatar = $this->getAvatarUrl($data->opponentAvatar);
        $presenter->status = $data->challengeDetails->status;
        $presenter->gameModeId = $data->gameModeId;
        $presenter->gameModeName = $data->gameModeName;

        return response()->json($presenter);
    }

}
