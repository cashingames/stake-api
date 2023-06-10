<?php

namespace App\Services;

use App\Http\ResponseHelpers\DailyRewardResponse;
use App\Jobs\ReactivateUserReward;
use App\Models\Reward;
use App\Models\RewardBenefit;
use App\Models\User;
use App\Models\UserReward;
use Carbon\Carbon;

class DailyRewardService
{
    public function shouldShowDailyReward(User $user)
    {

        $userLastRecord = $user->rewards()
            ->orderBy('reward_date', 'desc')
            ->withPivot('reward_count', 'reward_date', 'reward_milestone', 'release_on')
            ->first();

        $userRewardRecordCount = $user->rewards()->count();

        if ($userRewardRecordCount == 0) {
            $reward = Reward::where('name', 'daily_rewards')->first();
            $user->rewards()->attach($reward->id, [
                'reward_count' => 0,
                'reward_date' => now(),
                'release_on' => now(),
                'reward_milestone' => 1,
            ]);
            $rewardClaimableDay = $this->getTodayReward();
            return response()->json([
                "shouldShowPopup" => true,
                'reward' => $rewardClaimableDay
            ], 200);
        }

        if ($userRewardRecordCount > 0 && $userRewardRecordCount <= 7) {
            $userLastRewardClaimDate = Carbon::parse($userLastRecord->pivot->reward_date);
            $currentDate = Carbon::now();
            if ($userLastRewardClaimDate->isSameDay($currentDate)) {
                return response()->json([
                    "shouldShowPopup" => false,
                    'reward' => []
                ], 200);
            }
            $userRewardCount = $userLastRecord->pivot->reward_count;
            if ($userRewardCount == 0 && !$userLastRewardClaimDate->isSameDay($currentDate)) {
                $rewardClaimableDay = $this->getTodayReward();
                return response()->json([
                    "shouldShowPopup" => true,
                    'reward' => $rewardClaimableDay
                ], 200);
            }
            if ($userLastRewardClaimDate->diffInDays($currentDate) > 1) {
                $this->missDailyReward();
                dispatch(new ReactivateUserReward());
                return response()->json([
                    "shouldShowPopup" => false,
                    'reward' => []
                ], 200);
            }
            if ($userRewardCount == -1) {
                dispatch(new ReactivateUserReward());
                return response()->json([
                    "shouldShowPopup" => false,
                    'reward' => []
                ], 200);
            }
        } else {
            return response()->json([
                "shouldShowPopup" => false,
                'reward' => []
            ], 200);
        }
    }

    private function getTodayReward()
    {
        $rewardClaimableDays = RewardBenefit::get();
        $data = [];
        $response = new DailyRewardResponse();
        foreach ($rewardClaimableDays as $rewardEachDay) {
            $data[] = $response->transform($rewardEachDay);
        }
        return $data;
    }

    public function missDailyReward()
    {
        $user = auth()->user();
        $userLastRecord = $user->rewards()
            ->wherePivot('reward_count', 0)
            ->withPivot('reward_count', 'reward_date', 'release_on', 'reward_milestone')
            ->first();

        if ($userLastRecord) {
            $userLastRecord->pivot->reward_count = -1;
            $userLastRecord->pivot->save();

            $userClaimedRewards = UserReward::where('user_id', $user->id)->where('reward_count', 1)->get();
            foreach ($userClaimedRewards as $userClaimedReward) {
                $userClaimedReward->delete();
            }
        }
    }
}
