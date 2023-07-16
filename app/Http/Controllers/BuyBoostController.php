<?php

namespace App\Http\Controllers;

use App\Actions\Wallet\BuyBoostFromWalletAction;
use App\Http\Requests\BuyBoostRequest;
use App\Http\ResponseHelpers\ResponseHelper;
use Illuminate\Http\Request;

class BuyBoostController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        BuyBoostFromWalletAction $buyBoostAction,
        Request $request,
        BuyBoostRequest $requestModel
    ) {

        $requestModel->validated();
       
        $buyBoostAction->execute($request, auth()->user());


        return ResponseHelper::success(true, 'Boost Bought');
    }
}
