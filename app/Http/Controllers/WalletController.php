<?php

namespace App\Http\Controllers;

use App\WalletTransaction;

class WalletController extends BaseController
{

    public function me()
    {
        $data = [
            'wallet' => auth()->user()->wallet
        ];
        return $this->sendResponse($data, 'User wallet details');
    }

    public function transactions()
    {
        $data = [
            'transactions' => auth()->user()->transactions
        ];
        return $this->sendResponse($data, 'Wallet transactions information');
    }

    public function verifyTransaction(string $reference)
    {
        $wallet = auth()->user()->wallet;
        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type' => 'CREDIT',
            'amount' => 10,
            'wallet_type' => 'CASH',
            'description' => 'Cash topup',
            'reference' => $reference
        ]);
        return $this->sendResponse(true, 'Payment was successful');
    }
}
