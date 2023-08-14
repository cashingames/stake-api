<?php

namespace App\Actions\Cashdrop;

use App\Repositories\Cashingames\CashdropRepository;

class GetCashDropDataAction
{
    public function __construct(
        private readonly CashdropRepository $cashdropRepository,
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