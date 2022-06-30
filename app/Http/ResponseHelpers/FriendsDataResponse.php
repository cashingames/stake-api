<?php

namespace App\Http\ResponseHelpers;

use \Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class FriendsDataResponse
{
    public int $id;
    public string $username;
    public int $fullName;
    public $avatar;

    public function transform($friends): JsonResponse
    {

        $response = [];

        foreach ($friends as $data) {
            $presenter = new FriendsDataResponse;
            $presenter->id = $data->id;
            $presenter->username = $data->username;
            $presenter->avatar = $this->getAvatarUrl($data->avatar);

            $response[] = $presenter;
        }

        return response()->json($response);
    }

    private function getAvatarUrl($avatar)
    {
        if ($avatar !== null) {
            return config('app.url') . '/' . $avatar;
        }
        return $avatar;
    }
}
