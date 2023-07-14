<?php

namespace App\Actions\Wallet;

use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Models\Wallet;
use \App\Repositories\Cashingames\WalletRepository;
use App\Services\Bonuses\RegistrationBonus\RegistrationBonusService;
use Illuminate\Http\Request;

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
