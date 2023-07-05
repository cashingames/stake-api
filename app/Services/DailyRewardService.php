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

        $userRewardRecordCount = UserReward::where('user_id', $user->id)->count();
        if ($userRewardRecordCount == 0) {
            $reward = Reward::where('name', 'daily_rewards')->first();
            if ($reward) {
                UserReward::create([
                    'user_id' => $user->id,
                    'reward_id' => $reward->id,
                    'reward_count' => 0,
                    'reward_date' => now(),
                    'release_on' => now(),
                    'reward_milestone' => 1,
                ]);
            }
            return $this->returnRewardPayload($this->getTodayReward(), true);
        }

        if ($userRewardRecordCount > 0 && $userRewardRecordCount <= 7) {
            $userLastRecord = UserReward::where('user_id', $user->id)->latest()->first();
            $userLastRewardClaimDate = Carbon::parse($userLastRecord->reward_date);
            $currentDate = Carbon::now();

            if ($userLastRewardClaimDate->isSameDay($currentDate)) {

                return $this->returnRewardPayload([], false);
            }

            if ($userLastRewardClaimDate->diffInDays($currentDate) > 1) {
                if ($userLastRecord->reward_count >= 0) {
                    $this->missDailyReward();
                }
                return $this->returnRewardPayload([], false);
            }

            $userRewardCount = $userLastRecord->reward_count;

            if ($userRewardCount >= 0) {
                return $this->returnRewardPayload($this->getTodayReward(), true);
            }

            if ($userRewardCount == -1) {

                UserReward::where('user_id', $user->id)->where('reward_count', -1)
                    ->update([
                        'reward_count' => 0,
                        'reward_milestone' => 1,
                        'reward_date' => now(),
                        'release_on' => now(),
                    ]);
                return $this->returnRewardPayload($this->getTodayReward(), true);
            }
        } else {
            return $this->returnRewardPayload([], false);
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
        $userLastRecord = UserReward::where('user_id', $user->id)
            ->where('reward_count', 0)
            ->first();

        if (!is_null($userLastRecord)) {
            $userLastRecord->reward_count = -1;
            $userLastRecord->save();

            $userClaimedRewards = UserReward::where('user_id', $user->id)->where('reward_count', 1)->get();
            foreach ($userClaimedRewards as $userClaimedReward) {
                $userClaimedReward->delete();
            }
        }
    }

    private function returnRewardPayload(array $data, bool $shouldShowPopup)
    {
        return response()->json([
            "shouldShowPopup" => $shouldShowPopup,
            'reward' => $data
        ], 200);
    }
}
