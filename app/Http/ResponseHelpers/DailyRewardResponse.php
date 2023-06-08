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
        $users = User::all();
        foreach ($users as $user) {
            $userLastRecord = $user->rewards()
            ->wherePivot('reward_count', 0)
            ->latest('pivot_created_at')
            ->withPivot('reward_count', 'reward_date', 'release_on', 'reward_milestone')
            ->first();
            $userRewardRecordCount = $userLastRecord->pivot->reward_milestone;
            $userRecord = UserReward::where('user_id', $user->id)->where('reward_count', 1)->where('reward_milestone', $reward->reward_benefit_id)->first();
            if ($reward->reward_benefit_id == $userRewardRecordCount && !$userRecord) {
                return true;
            } else{
                return false;
            }
        }
    }
 
    private function isClaimed($reward)
    {
        $users = User::all();
        foreach ($users as $user) {
            $userRecords = UserReward::where('user_id', $user->id)->where('reward_count', 1)->where('reward_milestone', $reward->reward_benefit_id)->get();
            foreach($userRecords as $userRecord){
                return $userRecord->reward_milestone == $reward->reward_benefit_id;
            }
        }
    }
}
