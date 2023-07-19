<?php

namespace App\Actions\Wallet;

use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Models\Wallet;
use App\Repositories\Cashingames\BonusRepository;
use \App\Repositories\Cashingames\WalletRepository;
use App\Services\Bonuses\RegistrationBonus\RegistrationBonusService;
use Illuminate\Support\Facades\DB;

class DebitWalletForStaking
{
    public function __construct(
        private readonly WalletRepository $walletRepository,
        private RegistrationBonusService $registrationBonusService,
        private BonusRepository $bonusRepository
    ) {
    }
    public function execute(Wallet $wallet, float $amount, WalletBalanceType $walletType): float
    {
        $balanceToDeduct = "";
        $action = WalletTransactionAction::StakingPlaced->value;

        if ($walletType == WalletBalanceType::BonusBalance && $wallet->bonus >= $amount) {

            $balanceToDeduct = "bonus";
            $description = "Bonus Staking of " . $amount;

            DB::beginTransaction();
            $this->bonusRepository->deductFromUserBonuses($amount);
            $this->walletRepository->debit($wallet, $amount, $description, null, $balanceToDeduct, $action);
            DB::commit();
            return $wallet->bonus;
        }

        if ($wallet->non_withdrawable < $amount) {
            $amount = $wallet->non_withdrawable;
        }

        $balanceToDeduct = "non_withdrawable";
        $description = 'Single game stake debited';

        $this->walletRepository->debit($wallet, $amount, $description, null, $balanceToDeduct, $action);
        return $wallet->non_withdrawable;
    }

}
