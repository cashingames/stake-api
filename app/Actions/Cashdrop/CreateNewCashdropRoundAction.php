<?php

namespace App\Actions\Cashdrop;

use App\Models\Cashdrop;
use App\Repositories\Cashingames\CashdropRepository;

class CreateNewCashdropRoundAction
{
    public function __construct(
        private readonly CashdropRepository $cashdropRepository,
    ) {
    }

    public function execute(Cashdrop $cashdrop): void
    {
        $this->cashdropRepository->createCashdropRound($cashdrop);
    }
}
