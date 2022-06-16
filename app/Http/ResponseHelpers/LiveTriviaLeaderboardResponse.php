<?php

namespace App\Http\ResponseHelpers;

use \Illuminate\Http\JsonResponse;

class LiveTriviaLeaderboardResponse
{
    public int $points;
    public string $username;
    public string $first_name;
    public string $last_name;
    public int $user_id;


    public function transform($data): JsonResponse
    {

        $response = [];

        foreach ($data as $data) {
            $presenter = new LiveTriviaLeaderboardResponse;
            $presenter->user_id = $data->user_id;
            $presenter->points = $data->points;
            $presenter->username = $data->username;
            $presenter->first_name = $data->first_name;
            $presenter->last_name = $data->last_name;

            $response[] = $presenter;
        }
        
        return response()->json($response);
    }
}
