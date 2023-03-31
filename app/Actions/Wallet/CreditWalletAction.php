<?php

namespace App\Actions\Wallet;

use App\Models\Wallet;
use App\Repositories\Cashingames\WalletRepository;

class CreditWalletAction
{

    public function __construct(
        private readonly WalletRepository $walletRepository
    ) {
    }
    public function execute(Wallet $wallet, float $amount, string $description): void
    {
        $this->walletRepository->credit($wallet, $amount, $description, null);
    }
}
