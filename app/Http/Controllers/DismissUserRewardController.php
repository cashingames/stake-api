<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DismissUserRewardController extends Controller
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
            $userLastRecord->pivot->reward_count = -1;
            $userLastRecord->pivot->save();
        }
    }
}
