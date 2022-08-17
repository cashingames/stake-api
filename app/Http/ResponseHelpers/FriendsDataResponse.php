<?php

namespace App\Http\ResponseHelpers;

use \Illuminate\Http\JsonResponse;
use App\Traits\Utils\AvatarUtils;


class FriendsDataResponse
{

    use AvatarUtils;

    public int $id;
    public string $username;
    public int $fullName;
    public $avatar;


    public function transform($friends): JsonResponse
    {

        $response = [];

        foreach ($friends as $friend) {
            $presenter = new FriendsDataResponse;

            $presenter->id = $friend->id;
            $presenter->username = $friend->username;
            $presenter->avatar = $this->getAvatarUrl($friend->profile->avatar ?? '');

            $response[] = $presenter;
        }

        return response()->json($response);
    }
}
