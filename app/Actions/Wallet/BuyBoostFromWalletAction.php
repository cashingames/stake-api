<?php

namespace App\Actions\Wallet;

use \App\Repositories\Cashingames\WalletRepository;

class BuyBoostFromWalletAction
{
    public function __construct(
        private readonly WalletRepository $walletRepository,
    ) {
    }
    public function execute($request, $user)
    {
        $this->walletRepository->buyBoostFromWallet($request, $user);
    }
}
