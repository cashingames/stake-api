<?php

namespace App\Actions\Bonus;

use App\Repositories\Cashingames\BonusRepository;
use App\Services\Bonuses\WeeklyBonuses\WeeklyLossCashbackService;

class GiveStakeLossCashback
{
    public function __construct(
        private readonly BonusRepository $bonusRepository,
       
    ) {
    }

    public function execute()
    {
        $start = now()->startOfWeek();
        $end = now()->endOfWeek() ;

        $usersWithLosses = $this->bonusRepository->getUserStakeLossBetween($start , $end);

        $this->bonusRepository->giveCashback($usersWithLosses);
    }
}