<?php

namespace App\Http\ResponseHelpers;

use \Illuminate\Http\JsonResponse;
use App\Traits\Utils\AvatarUtils;

class LiveTriviaLeaderboardResponse
{
    use AvatarUtils;

    public int $points;
    public string $username;
    public int $userId;
    public int $duration;
    public $avatar;

    public function transform($leaders): JsonResponse
    {

        $response = [];

        foreach ($leaders as $data) {
            $presenter = new LiveTriviaLeaderboardResponse;
            $presenter->userId = $data->user_id;
            $presenter->points = $data->points;
            $presenter->username = $data->username;
            $presenter->duration = $data->duration;
            $presenter->avatar = $this->getAvatarUrl($data->avatar);

            $response[] = $presenter;
        }

        return response()->json($response);
    }
}
