<?php

namespace App\Http\Controllers;

use App\Models\LiveTriviaUserPayment;
use App\Models\Trivia;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LiveTriviaEntrancePaymentController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    function __invoke(Request $request)
    {
        $request->validate([
            'liveTriviaId' => 'required|exists:trivias,id'
        ]);

        $liveTrivia = Trivia::find($request->liveTriviaId);

        if ($this->user->wallet->non_withdrawable_balance < $liveTrivia->entry_fee) {
            return $this->sendError('Insufficient Wallet Balance', 'Insufficient Wallet Balance');
        }

        if(LiveTriviaUserPayment::where('trivia_id', $request->liveTriviaId)->where('user_id', $this->user->id)->exists()){
            return $this->sendResponse('Payment successful', 'Payment successful');
        }

        $this->user->wallet->non_withdrawable_balance -= $liveTrivia->entry_fee;
        $this->user->wallet->save();

        WalletTransaction::create([
            'wallet_id' =>  $this->user->wallet->id,
            'transaction_type' => 'DEBIT',
            'amount' => $liveTrivia->entry_fee,
            'balance' => $this->user->wallet->non_withdrawable_balance,
            'description' => 'Paid entrance fee for live trivia',
            'reference' => Str::random(10),
        ]);

        LiveTriviaUserPayment::create([
            'trivia_id' => $liveTrivia->id,
            'user_id' => $this->user->id
        ]);
        return $this->sendResponse('Payment successful', 'Payment successful');
    }
}
