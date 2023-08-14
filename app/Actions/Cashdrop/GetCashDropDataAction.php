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
    ): mixed {

        return $this->cashdropRepository->getCashdropData();
    }
}