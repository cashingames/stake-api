<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AchievementBadge;
use Illuminate\Support\Facades\Log;

use stdClass;

class AchievementBadeController extends BaseController
{
    //
    public function getAchievements(Request $request){
        $result = new stdClass;

        $result->myAchievementBadges = $request->user()->userAchievementBadge();
        $result->allAchievementBadges = AchievementBadge::get();

        return $this->sendResponse($result, "Achievements");
    }
}
