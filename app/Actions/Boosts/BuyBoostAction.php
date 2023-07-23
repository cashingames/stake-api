<?php

namespace App\Actions\Boosts;

use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Enums\WalletTransactionType;
use App\Repositories\Cashingames\BoostRepository;
use App\Repositories\Cashingames\WalletRepository;
use App\Repositories\Cashingames\WalletTransactionDto;

class BuyBoostAction
{
    public function __construct(
        private readonly BoostRepository $boostRepository,
        private readonly WalletRepository $walletRepository,
    ) {
    }

    public function execute(
        int $boostId,
        WalletBalanceType $walletType,
    ): mixed {

        /**
         * @TODO: Action should not know about authentication
         **/
        $boost = $this->boostRepository->addUserBoost($boostId, auth()->id());


        $this->walletRepository->addTransaction(
            new WalletTransactionDto(
                auth()->id(),
                $boost->price,
                "Bought boost {$boost->name}",
                $walletType,
                WalletTransactionType::Debit,
                WalletTransactionAction::BoostBought,
            )
        );

        return 0.0;
    }
}