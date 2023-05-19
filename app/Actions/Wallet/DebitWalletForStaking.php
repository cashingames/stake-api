<?php

namespace App\Actions\Wallet;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use \App\Repositories\Cashingames\WalletRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DebitWalletForStaking
{
    public function __construct(
        private readonly WalletRepository $walletRepository
    ) {
    }
    public function execute(Wallet $wallet, float $amount): float
    {
        $balanceToDeduct = " ";

        if ($wallet->hasBonus() &&  $wallet->bonus > $amount) {

            $balanceToDeduct = "bonus";
            $description = "Bonus Staking of ".$amount;

            // dd($balanceToDeduct);
            $this->walletRepository->debit($wallet, $amount, $description, null, $balanceToDeduct);
            return $wallet->bonus;
        }
        $balanceToDeduct = "non_withdrawable";
        $description = 'Placed a staking of ' . $amount;

        $this->walletRepository->debit($wallet, $amount, $description, null, $balanceToDeduct);
        return $wallet->non_withdrawable;
    }
}
