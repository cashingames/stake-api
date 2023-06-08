<?php

namespace App\Http\ResponseHelpers;

use App\Models\RewardBenefit;
use App\Models\User;
use App\Models\UserReward;
use Carbon\Carbon;

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
            'can_claim' => $this->canClaim($reward),
            'is_claimed' => $this->isClaimed($reward)
        ];
    }

    private function canClaim($reward)
    {
        $user = auth()->user();
        $userLastRecord = UserReward::where('user_id', $user->id)->where('reward_count', 0)->first();
        if ($userLastRecord && $reward->reward_benefit_id == $userLastRecord->reward_milestone) {
            $userRecord = UserReward::where('user_id', $user->id)
                ->where('reward_count', 1)
                ->where('reward_milestone', $reward->reward_benefit_id)
                ->first();
            if (!$userRecord) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }


    private function isClaimed($reward)
    {
        $user = auth()->user();
        $userRecords = UserReward::where('user_id', $user->id)->where('reward_count', 1)->where('reward_milestone', $reward->reward_benefit_id)->get();
        foreach ($userRecords as $userRecord) {
            return $userRecord->reward_milestone == $reward->reward_benefit_id;
        }
    }
}
