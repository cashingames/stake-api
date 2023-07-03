<?php

namespace App\Actions\Bonus;

use App\Repositories\Cashingames\BonusRepository;
use App\Services\Bonuses\WeeklyBonuses\WeeklyLossCashbackService;

class GiveWeeklyLossCashback
{
    public function __construct(
        private readonly BonusRepository $bonusRepository,
        private readonly WeeklyLossCashbackService $cashbackService
    ) {
    }

    public function execute()
    {
        $start = now()->endOfWeek();
        $end = now()->startOfWeek();

        $usersWithLosses = $this->bonusRepository->getWeeklyUserLosses($start , $end);

        // dd($usersWithLosses);

        $this->cashbackService->giveCashback($usersWithLosses);
    }
}