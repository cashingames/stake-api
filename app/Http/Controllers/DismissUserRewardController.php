<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserReward;
use App\Services\DailyRewardService;

class DismissUserRewardController extends Controller
{
    public function __invoke()
    {
        $service = new DailyRewardService();
        $service->missDailyReward();
    }
}
