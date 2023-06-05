<?php

namespace App\Http\Controllers;

use App\Models\Boost;
use App\Models\Reward;
use App\Models\RewardBenefit;
use App\Models\User;

class ClaimUserRewardController extends Controller
{
    public function __invoke()
    {

        $user = auth()->user();
        $userLastRecord = $user->rewards()
            ->wherePivot('reward_count', 0)
            ->latest('pivot_created_at')
            ->withPivot('reward_count', 'reward_date', 'release_on', 'reward_milestone')
            ->first();
            if ($userLastRecord) {
                $userLastRecord->pivot->reward_count = 1;
                $userLastRecord->pivot->save();
            }

            $userRewardRecordCount = $user->rewards()->count();

        $rewardClaimableDays = RewardBenefit::where('reward_benefit_id', $userRewardRecordCount)->get();
        foreach ($rewardClaimableDays as $rewardEachDay) {
            if ($rewardEachDay->reward_type == 'boost' && $userRewardRecordCount > 0) {
                $userBoost = $user->boosts()->where('name', $rewardEachDay->reward_name)->first();
              
                if ($userBoost === null) {
                    $user->boosts()->create([
                        'user_id' => $user->id,
                        'boost_id' => Boost::where('name', $rewardEachDay->reward_name)->first()->id,
                        'boost_count' => $rewardEachDay->reward_count,
                        'used_count' => 0,
                    ]); 
                } else {
                    $userBoost->update(['boost_count' => $userBoost->boost_count + $rewardEachDay->reward_count]);
                }
            }

            if ($rewardEachDay->reward_type == 'coins') {
                $userCoin = $user->userCoins()->firstOrNew();
                $userCoin->coins_value = $userCoin->coins_value + $rewardEachDay->reward_count;
                $userCoin->save();

                $user->coinsTransaction()->create([
                    'user_id' => $user->id,
                    'transaction_type' => 'CREDIT',
                    'description' => 'Daily reward coins awarded',
                    'value' => $rewardEachDay->reward_count,
                ]);
            }
        }
        $reward = Reward::where('name','daily_rewards')->first();

        $user->rewards()->attach($reward->id, [
            'reward_count' => 0,
            'reward_date' => now(),
            'release_on' => now(),
            'reward_milestone' => $userRewardRecordCount + 1
        ]);
    }
}
