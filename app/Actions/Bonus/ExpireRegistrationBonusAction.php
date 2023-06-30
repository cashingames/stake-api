<?php

namespace App\Actions\Bonus;

use App\Repositories\Cashingames\BonusRepository;

class ExpireRegistrationBonusAction
{

    public function __construct(
        private readonly BonusRepository $bonusRepository
    ) {
    }

    public function execute()
    {
        $activeRegistrationBonuses = $this->bonusRepository->getActiveUserRegistrationBonusesToExpire();

        $this->bonusRepository->expireBonuses($activeRegistrationBonuses);
    }
}