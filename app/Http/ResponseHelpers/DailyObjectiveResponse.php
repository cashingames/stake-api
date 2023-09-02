<?php

namespace App\Http\ResponseHelpers;

use App\Models\DailyObjective;
use App\Models\User;
use App\Models\UserDailyObjective;

class DailyObjectiveResponse
{
    public function transform($dailyObjective)
    {
        // dd($dailyObjective->objective);
        return [
            'type' =>$dailyObjective->objective->reward_type,
            'count' => $dailyObjective->milestone_count,
            'icon' => $dailyObjective->objective->icon,
            'description' => $dailyObjective->objective->description,
            'achieved' => $this->showIsAchieved($dailyObjective->objective),
        ];
    }

    private function showIsAchieved($objective)
    {
        $dailyObjectiveId = DailyObjective::whereDate('created_at', now()->startOfDay())->where('objective_id', $objective->id)->first();
            $isAchieved = UserDailyObjective::where('user_id', auth()->user()->id)->where('daily_objective_id', $dailyObjectiveId->id)->first();
                if ($isAchieved->is_achieved) {
                    return true;
                } else {
                    return false;
                }
    }
}
