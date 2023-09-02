<?php

namespace App\Http\Controllers;

use App\Models\DailyObjective;
use App\Models\Objective;
use App\Models\UserDailyObjective;
use App\Services\DailyObjectiveService;

class GetDailyObjectiveController extends BaseController
{
    public function __invoke()
    {
    $user = auth()->user();
     $showDailyObjective = new DailyObjectiveService();
     return $showDailyObjective->dailyObjective($user);
    }
}
