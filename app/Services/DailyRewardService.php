<?php

namespace App\Services;

use App\Jobs\ReactivateUserReward;
use App\Models\Reward;
use App\Models\RewardBenefit;
use App\Models\User;
use Carbon\Carbon;


//create new column for reward_milestone in user_rewards table
//create new image column in reward_benefits table
class DailyRewardService
{
    public function shouldShowDailyReward(User $user)
    {
        $userRewardRecordCount = $user->rewards()->count();
        $reward = Reward::find(1);
        $userLastRecord = $user->rewards()->latest('pivot_created_at')->withPivot('reward_count', 'reward_date', 'reward_milestone', 'release_on')->first();
        $userRewardCount = $userLastRecord->pivot->reward_count;
        //First day

        if ($userRewardRecordCount == 0) {
            $newUserRewardRecord = $user->rewards()->attach($reward->id, [
                'reward_count' => 0,
                'reward_date' => now(),
                'release_on' => now(),
                'reward_milestone' => 1,
            ]);
            $rewardClaimableDay = $this->getTodayReward(1);
            return response()->json([
                "shouldShowPopup" => true,
                'reward' => $rewardClaimableDay], 200);
        }

        if ($userRewardRecordCount > 0 && $userRewardRecordCount <= 7) {
            $userLastRewardClaimDate = Carbon::parse($userLastRecord->pivot->reward_date);
            $currentDate = Carbon::now();
            if ($userLastRewardClaimDate->isSameDay($currentDate)) {
                return response()->json([
                    "shouldShowPopup" => false,
                    'reward' => []], 200);
            }
            if ($userRewardCount == 0 && !$userLastRewardClaimDate->isSameDay($currentDate)) {
                $rewardClaimableDay = $this->getTodayReward($userRewardRecordCount);
                return response()->json([
                    "shouldShowPopup" => true,
                    'reward' => $rewardClaimableDay], 200);
            }
            if ($userRewardCount == -1) {
                dispatch(new ReactivateUserReward());
                return response()->json([
                    "shouldShowPopup" => false,
                    'reward' => []], 200);
            }
        } else {
            return false;
        }
    }

    private function getTodayReward($day)
    {
        $rewardClaimableDays = RewardBenefit::where('reward_benefit_id', $day)->get();
        $data = [];
        foreach ($rewardClaimableDays as $rewardEachDay) {
            $data['type'] = $rewardEachDay->reward_type;
            $data['count'] = $rewardEachDay->reward_count;
        }

        return $data;
    }
}
