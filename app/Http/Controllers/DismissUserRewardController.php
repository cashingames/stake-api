<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserReward;

class DismissUserRewardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $userClaimedRewards = UserReward::where('user_id', $user->id)->where('reward_count', 1)->get();
        foreach($userClaimedRewards as $userClaimedReward){
            $userClaimedReward->delete();
        }

        $userLastRecord = $user->rewards()
            ->wherePivot('reward_count', 0)
            ->latest('pivot_created_at')
            ->withPivot('reward_count', 'reward_date', 'release_on', 'reward_milestone')
            ->first();

        if ($userLastRecord) {
            $userLastRecord->pivot->reward_count = -1;
            $userLastRecord->pivot->save();
        }
    }
}
