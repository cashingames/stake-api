<?php

namespace App\Actions\Wallet;

use App\Models\Wallet;
use \App\Repositories\Cashingames\WalletRepository;

class DebitWalletAction
{
    public function __construct(
        private readonly WalletRepository $walletRepository
    ) {
    }
    public function execute(Wallet $wallet, float $amount, string $description): void
    {
        $this->walletRepository->debit($wallet, $amount, $description, null);
    }
}
