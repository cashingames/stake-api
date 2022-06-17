<?php

namespace App\Http\ResponseHelpers;

use \Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class LiveTriviaLeaderboardResponse
{
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
            $presenter->duration = $this->getGameDurationInSeconds($data->startTime, $data->endTime);
            $presenter->avatar = $this->getAvatarUrl($data->avatar);

            $response[] = $presenter;
        }

        return response()->json($response);
    }

    private function getGameDurationInSeconds($startTime, $endTime)
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        return $start->diffInSeconds($end);
    }

    private function getAvatarUrl($avatar)
    {   
        if ($avatar !== null){
            return config('app.url').'/'.$avatar;
        }
        return $avatar;
    }
}
