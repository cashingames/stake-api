<?php

namespace App\Actions\Wallet;

use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Enums\WalletTransactionType;
use App\Models\Wallet;
use \App\Repositories\Cashingames\WalletRepository;
use App\Repositories\Cashingames\WalletTransactionDto;
use App\Services\Bonuses\RegistrationBonus\RegistrationBonusService;

class DebitWalletForStaking
{
    public function __construct(
        private readonly WalletRepository $walletRepository,
        private RegistrationBonusService $registrationBonusService
    ) {
    }
    public function execute(Wallet $wallet, float $amount, WalletBalanceType $walletType): float
    {
        if (
            $walletType == WalletBalanceType::BonusBalance &&
            null != $bonus = $this->registrationBonusService->activeRegistrationBonus($wallet->user)
        ) {
            $bonus->amount_remaining_after_staking -= $amount;
            $bonus->save();
        }


        $result = $this->walletRepository->addTransaction(
            new WalletTransactionDto(
                $wallet->user_id,
                $amount,
                'Single game stake debited',
                $walletType,
                WalletTransactionType::Debit,
                WalletTransactionAction::StakingPlaced,
            )
        );

        return $result->balance;
    }

}