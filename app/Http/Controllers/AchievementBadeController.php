<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AchievementBadge;
use Illuminate\Support\Facades\Log;
use App\Http\ResponseHelpers\AchievementBadgeResponse;

use stdClass;

class AchievementBadeController extends BaseController
{
    //
    public function getAchievements(Request $request){
        // $result = new stdClass;

        $myAchievementBadges = $request->user()->userAchievementBadge();
        $allAchievementBadges = AchievementBadge::get();

        return (new AchievementBadgeResponse())->transform($myAchievementBadges, $allAchievementBadges);
    }
}
