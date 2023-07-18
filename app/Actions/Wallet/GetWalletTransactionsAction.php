<?php

namespace App\Actions\Wallet;
use App\Models\Wallet;
use \App\Repositories\Cashingames\WalletRepository;

class GetWalletTransactionsAction
{
    public function __construct(
        private readonly WalletRepository $walletRepository
    ) {
    }
    public function execute(Wallet $wallet, string $walletType)
    {
        return $this->walletRepository->getWalletTransactions($wallet, $walletType);
    }
}
