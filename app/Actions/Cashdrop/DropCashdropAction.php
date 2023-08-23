<?php

namespace App\Actions\Cashdrop;

use App\Models\CashdropRound;
use App\Repositories\Cashingames\CashdropRepository;
use App\Repositories\Cashingames\WalletRepository;

class DropCashdropAction
{
    public function __construct(
        private readonly CashdropRepository $cashdropRepository,
        private readonly WalletRepository $walletRepository,
        private readonly CreateNewCashdropRoundAction $createNewCashdropRoundAction,
       
    ) {
    }

    public function execute(CashdropRound $cashdropRound, $env): void {

        $cashdrop = $this->cashdropRepository->creditWinner($this->walletRepository, $cashdropRound);
        $this->createNewCashdropRoundAction->execute($cashdrop, $env);
    }
}