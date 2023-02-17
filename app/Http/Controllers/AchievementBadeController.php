<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use stdClass;

class AchievementBadeController extends Controller
{
    //
    public function getAchievements(){
        $result = new stdClass;

        $result->myAchievementBadges = $this->user->userAchievementBadge();
        $result->allAchievementBadges = $this->user->achievementBadge();

        return $this->sendResponse($result, "Achievements");
    }
}
