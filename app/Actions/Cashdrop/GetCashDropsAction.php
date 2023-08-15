<?php

namespace App\Actions\Cashdrop;

use App\Repositories\Cashingames\CashdropRepository;

class GetCashDropsAction
{
    public function __construct(
        private readonly CashdropRepository $cashdropRepository,
    ) {
    }

    public function execute(
    ): array {

        return $this->cashdropRepository->getCashdrops();
    }
}