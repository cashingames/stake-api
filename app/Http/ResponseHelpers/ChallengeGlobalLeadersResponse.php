<?php

namespace App\Http\ResponseHelpers;

use \Illuminate\Http\JsonResponse;
use App\Traits\Utils\AvatarUtils;


class ChallengeGlobalLeadersResponse
{

    use AvatarUtils;

    public int $winner;
    public int $wins;
    public $avatar;
    public string $username;


    public function transform($data): JsonResponse
    {
        $response = [];
        foreach ($data as $leader) {
            $presenter = new ChallengeGlobalLeadersResponse;

            $presenter->winner = $leader->winner;
            $presenter->wins = $leader->wins;
            $presenter->avatar = $this->getAvatarUrl($leader->avatar);
            $presenter->username = $leader->username;

            $response[] = $presenter;
        }

        return response()->json( $response);
    }
}
