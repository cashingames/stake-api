<?php

namespace App\Actions\Bonus;

use App\Repositories\Cashingames\BonusRepository;

class GiveLossCashbackAction
{
    public function __construct(
        private readonly BonusRepository $bonusRepository,
    ) {
    }

    public function execute()
    {
        $start = now()->startOfWeek();
        $end = now()->endOfWeek();

        $usersWithLosses = $this->bonusRepository->getUsersLossBetween($start, $end);
        $this->bonusRepository->giveCashback($usersWithLosses);
    }
}