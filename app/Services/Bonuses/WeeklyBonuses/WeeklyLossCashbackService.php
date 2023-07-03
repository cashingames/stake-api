<?php

namespace App\Services\Bonuses\WeeklyBonuses;

use App\Models\Bonus;
use App\Enums\BonusType;
use App\Enums\WalletTransactionAction;
use App\Models\User;
use App\Models\UserBonus;
use App\Models\WalletTransaction;
use App\Repositories\Cashingames\BonusRepository;
use App\Services\Bonuses\BonusInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WeeklyLossCashbackService
{
    private $bonusRepository;
    private $bonus;

    public function __construct()
    {
        $this->bonusRepository = new BonusRepository;
        $this->bonus = Bonus::where('name', BonusType::WeeklyLossCashback->value)->first();
    }

    public function giveCashback(Collection $usersLosses)
    {

        $data = [];

        foreach ($usersLosses as $userLoss) {
            $data[] = [
                'user_id' => $userLoss->user_id,
                'bonus_id' => $this->bonus->id,
                'is_on' => true,
                'amount_credited' => abs($userLoss->cashback),
                'amount_remaining_after_staking' => abs($userLoss->cashback),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('user_bonuses')->insert($data);
    }
}
