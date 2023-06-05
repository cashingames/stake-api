<?php

namespace App\Http\ResponseHelpers;

class DailyRewardResponse
{
    public function transform($reward)
    {
        return [
            'type' => $reward->reward_type,
            'count' => $reward->reward_count,
            'icon' => $reward->icon,
            'day' => $reward->reward_benefit_id,
            'name' => $reward->reward_name,
        ];
    }
}