<?php

namespace App\Http\Controllers;

use App\Actions\Wallet\GetWalletTransactionsAction;
use App\Http\ResponseHelpers\WalletTransactionsResponse;
use Illuminate\Http\Request;

class GetUserTransactionsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, GetWalletTransactionsAction $transactionAction)
    {
        $transactions = $transactionAction->execute(auth()->user()->wallet, $request->wallet_type);

        return (new WalletTransactionsResponse())->transform($transactions)->original;
        
    }
}
