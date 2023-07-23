<?php

namespace App\Http\Controllers;

use App\Actions\Boosts\BuyBoostAction;
use App\Http\Requests\BuyBoostRequest;
use App\Enums\WalletBalanceType;

class BoostsController extends Controller
{
    public function buy(
        BuyBoostRequest $requestModel,
        BuyBoostAction $buyBoostAction,
    ) {

        $data = $requestModel->validated();

        $result = $buyBoostAction->execute(
            $data['id'],
            WalletBalanceType::from($data['wallet_type']),
        );

        return response()->json(['message' => 'Boost purchased successfully']);
    }
}