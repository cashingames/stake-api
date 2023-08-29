<?php

namespace App\Http\Controllers;

use App\Models\Boost;
use App\Models\Reward;
use App\Models\RewardBenefit;
use App\Models\UserBoost;
use Illuminate\Http\Request;

class ClaimHallmarkLevelRewardsController extends Controller
{
    public function __invoke(Request $request)
    {

        $request->validate([
            'level' => ['required', 'integer'],
        ]);

        $user = auth()->user();
        $levelRewards = Reward::where('name', 'level_rewards')->first()->id;
        $claimableRewards = RewardBenefit::where('reward_id', $levelRewards)->get();
        $data = [];
        foreach($claimableRewards as $reward){
            $boostId = Boost::where('name', $reward->reward_name)->first()->id;
            $userBoost = $user->boosts()->where('boost_id', $boostId)->first();

            if ($userBoost === null) {
                $user->boosts()->create([
                    'boost_id' => Boost::where('name', $reward->reward_name)->first()->id,
                    'boost_count' => $request->level,
                    'used_count' => 0,
                ]);
            } else {
                $userBoost->update(['boost_count' => $userBoost->boost_count +  $request->level]);
            }
        }
    }
}
