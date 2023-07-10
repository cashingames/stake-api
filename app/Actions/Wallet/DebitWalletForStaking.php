<?php

namespace App\Actions\Wallet;

use App\Enums\WalletTransactionAction;
use App\Models\Wallet;
use \App\Repositories\Cashingames\WalletRepository;
use App\Services\Bonuses\RegistrationBonus\RegistrationBonusService;

class DebitWalletForStaking
{
    public function __construct(
        private readonly WalletRepository $walletRepository,
        private RegistrationBonusService $registrationBonusService
    ) {
    }
    public function execute(Wallet $wallet, float $amount, $walletType): float
    {
        $balanceToDeduct = "";
        $action = WalletTransactionAction::StakingPlaced->value;
        
        if ($walletType == "bonus_balance" &&  $wallet->bonus >= $amount) {
            $hasRegistrationBonus = $this->registrationBonusService->hasActiveRegistrationBonus($wallet->user);
            if ($hasRegistrationBonus) {
                $registrationBonus = $this->registrationBonusService->activeRegistrationBonus($wallet->user);
                $this->handleRegistrationBonusDeduction($registrationBonus, $amount);
            }
            $balanceToDeduct = "bonus";
            $description = "Bonus Staking of " . $amount;

            $this->walletRepository->debit($wallet, $amount, $description, null, $balanceToDeduct, $action);
            return $wallet->bonus;
        }

        if ($wallet->non_withdrawable < $amount) {
            $amount = $wallet->non_withdrawable;
        }

        $balanceToDeduct = "non_withdrawable";
        $description = 'Placed a staking of ' . $amount;

        $this->walletRepository->debit($wallet, $amount, $description, null, $balanceToDeduct, $action);
        return $wallet->non_withdrawable;
    }

    private function handleRegistrationBonusDeduction($registrationBonus, $amount)
    {
        $registrationBonus->amount_remaining_after_staking -= $amount;
        $registrationBonus->save();
    }
}
