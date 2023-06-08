<?php

namespace App\Repositories\Cashingames;

use App\Models\Bonus;
use App\Models\User;
use App\Models\UserBonus;

class BonusRepository
{
    public function giveBonus(Bonus $bonus, User $user)
    {
        UserBonus::create([
            'user_id' => $user->id,
            'bonus_id' => $bonus->id
        ]);
    }

    public function activateBonus(Bonus $bonus, User $user, float $amount)
    {
        UserBonus::where('user_id', $user->id)
            ->where('bonus_id', $bonus->id)
            ->where('is_on', false)->update([
                'is_on' => true,
                'amount_credited' => $amount,
                'amount_remaining_after_staking' => $amount
            ]);
    }

    public function deactivateBonus(Bonus $bonus, User $user)
    {
        UserBonus::where('user_id', $user->id)
            ->where('bonus_id', $bonus->id)
            ->where('is_on', true)->update([
                'is_on' => false
            ]);
        $user->wallet->bonus = $user->wallet->bonus - $bonus->amount_remaining_after_staking;
        $user->wallet->save();
    }

    public function updateWonAmount(Bonus $bonus, User $user, float $amount)
    {

        $userBonus = UserBonus::where('user_id', $user->id)
            ->where('bonus_id', $bonus->id)
            ->where('is_on', true)->first();

        $userBonus->total_amount_won = $userBonus->total_amount_won + $amount;
        $userBonus->amount_remaining_after_staking = $userBonus->amount_remaining_after_staking + $amount;

        $userBonus->save();
    }
}
