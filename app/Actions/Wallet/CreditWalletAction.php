<?php

namespace App\Actions\Wallet;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreditWalletAction
{
    public function execute(Wallet $wallet, float $amount, string $description): void
    {
        DB::transaction(function () use ($wallet, $amount, $description) {
            $this->creditWallet($wallet, $amount, $description);
        });
    }

    private function creditWallet(Wallet $wallet, float $amount, string $description): void
    {
        $wallet->update([
            'non_withdrawable_balance' => $wallet->non_withdrawable_balance - $amount
        ]);

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type' => 'CREDIT',
            'amount' => $amount,
            'balance' => $wallet->non_withdrawable_balance,
            'description' => $description,
            'reference' => Str::random(10),
        ]);


    }
}
