<?php

namespace App\Http\ResponseHelpers;

class AchievementBadgeResponse{

    public function transform($myAchievement, $allAchievementBadges){
        $response = new AchievementBadgeResponse;
        $response->myAchievementBadges = $myAchievement;
        $response->allAchievementBadges = $allAchievementBadges;

        return response()->json($response);
    }
}
