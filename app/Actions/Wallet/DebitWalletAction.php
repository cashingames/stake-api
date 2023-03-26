<?php

namespace App\Actions\Wallet;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DebitWalletAction
{
    public function execute(Wallet $wallet, float $amount, string $description): void
    {
        DB::transaction(function () use ($wallet, $amount, $description) {
            $this->debitWallet($wallet, $amount, $description);
        });
    }

    private function debitWallet(Wallet $wallet, float $amount, string $description): void
    {
        $wallet->update([
            'non_withdrawable_balance' => $wallet->non_withdrawable_balance - $amount
        ]);

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type' => 'DEBIT',
            'amount' => $amount,
            'balance' => $wallet->non_withdrawable_balance,
            'description' => $description,
            'reference' => Str::random(10),
        ]);


    }
}
