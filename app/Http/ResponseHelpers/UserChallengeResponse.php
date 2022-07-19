<?php

namespace App\Http\ResponseHelpers;

use \Illuminate\Http\JsonResponse;


class UserChallengeResponse
{
    public string $playerUsername;
    public string $opponentUsername;
    public  $date;
    public string $subcategory;
     public int $challengeId;
    public $status;
   

    public function transform($challenges): JsonResponse
    {

        $response = [];
        foreach ($challenges as $data) {

            $presenter = new UserChallengeResponse;
            $presenter->subcategory = $data->name;
            $presenter->playerUsername = $data->username;
            $presenter->opponentUsername = $data->opponentUsername;
            $presenter->status = $data->status;
            $presenter->challengeId = $data->id;
            $presenter->date = $data->created_at;
            $response[] = $presenter;
        }

        return response()->json($response);
    }
}
