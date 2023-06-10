<?php

namespace App\Http\Controllers;

use App\Services\DailyRewardService;

class MissUserRewardController extends BaseController
{
    public function __invoke()
    {
        $service = new DailyRewardService();
        $service->missDailyReward();

        return $this->sendResponse('Reward Missed', 'Reward Missed');
    }
}
