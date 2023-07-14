<?php

namespace App\Http\Controllers;

use App\Enums\WalletTransactionAction;
use App\Http\ResponseHelpers\ResponseHelper;
use App\Models\Boost;
use App\Repositories\Cashingames\WalletRepository;
use Illuminate\Http\Request;

class BuyBoostController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(WalletRepository $walletRepository, Request $request)
    {
        $boost = Boost::find($request->boostId);

        if ($boost == null) {
            return ResponseHelper::error('Wrong boost selected');
        }

        $user = auth()->user();
        $wallet = $user->wallet;
        $walletType = 'non_withdrawable';

        if ($request->wallet_type == 'bonus_balance') {
            if ($wallet->bonus < ($boost->currency_value)) {
                return ResponseHelper::error('You do not have enough money in your bonus wallet.');
            }
            $walletType = 'bonus';
        } else {
            if ($wallet->non_withdrawable < ($boost->currency_value)) {
                return ResponseHelper::error('You do not have enough money in your deposit wallet.');
            }
        }

        $walletRepository->debit(
            $wallet,
            $boost->currency_value,
            ($boost->name . ' boost purchased'),
            null,
            $walletType,
            WalletTransactionAction::BoostBought->value
        );

        $userBoost = $user->boosts()->where('boost_id', $request->boostId)->first();

        if ($userBoost == null) {
            $user->boosts()->create([
                'user_id' => $user->id,
                'boost_id' => $request->boostId,
                'boost_count' => $boost->pack_count,
                'used_count' => 0
            ]);
        } else {
            $userBoost->update(['boost_count' => $userBoost->boost_count + $boost->pack_count]);
        }

        return ResponseHelper::success($wallet->non_withdrawable, 'Boost Bought');
    }
}
