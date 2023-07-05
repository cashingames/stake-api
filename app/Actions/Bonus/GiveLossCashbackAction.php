<?php

namespace App\Actions\Bonus;

use App\Enums\Bonus\CashbackAccrualDuration;
use App\Repositories\Cashingames\BonusRepository;

class GiveLossCashbackAction
{
    public function __construct(
        private readonly BonusRepository $bonusRepository,
    ) {
    }

    public function execute(CashbackAccrualDuration $duration)
    {
        $start = CashbackAccrualDuration::DAILY == $duration ?
            now()->startOfDay() :
            now()->startOfWeek();

        $end = CashbackAccrualDuration::DAILY == $duration ?
            now()->endOfDay() :
            now()->endOfWeek();

        $usersWithLosses = $this->bonusRepository->getUsersLossBetween($start, $end);
        $this->bonusRepository->giveCashback($usersWithLosses);
    }
}