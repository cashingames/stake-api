<?php

namespace App\Http\Controllers;

use App\Models\Boost;
use Illuminate\Http\Request;

class AdsRewardController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'adRewardType' => ['required', 'string'],
            'rewardCount' => ['required', 'integer'],
            'adRewardPrize' => ['required', 'string'],
        ]);

        $user = auth()->user();

        if ($request->input('adRewardType') == 'boost') {
            $boostId = Boost::where('name', $request->input('adRewardPrize'))->first()->id;
            $userBoost = $user->boosts()->where('boost_id', $boostId)->first();

            if ($userBoost === null) {
                $user->boosts()->create([
                    'boost_id' => Boost::where('name', $request->adRewardPrize)->first()->id,
                    'boost_count' => $request->input('rewardCount'),
                    'used_count' => 0,
                ]);
            } else {
                $userBoost->update(['boost_count' => $userBoost->boost_count + $request->input('rewardCount')]);
            }
        }

        if ($request->adRewardType == 'coins') {
            $userCoin = $user->userCoins()->firstOrNew();
            $userCoin->coins_value = $userCoin->coins_value + $request->rewardCount;
            $userCoin->save();

            $user->coinsTransaction()->create([
                'transaction_type' => 'CREDIT',
                'description' => 'In-app reward coins awarded',
                'value' => $request->input('rewardCount'),
            ]);
        }
        
         return response()->json([
           "message" => "Reward Earned"
        ], 200);
    }
}
