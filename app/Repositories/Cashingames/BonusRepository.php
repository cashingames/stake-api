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
                'amount_credited' => 500,
                'amount_remaining_after_staking' => $amount
            ]);
    }

    public function updateWonAmount(Bonus $bonus, User $user, float $amount)
    {

        $userBonus = UserBonus::where('user_id', $user->id)
            ->where('bonus_id', $bonus->id)
            ->where('is_on', true)->first();

        $userBonus->total_amount_won = $userBonus->total_amount_won + $amount;
        $userBonus->amount_remaining_after_withdrawal = $userBonus->amount_remaining_after_withdrawal + $amount;

        $userBonus->save();
    }

    public function updateAmountWithdrawn(Bonus $bonus, User $user, float $amount)
    {

        $userBonus = UserBonus::where('user_id', $user->id)
            ->where('bonus_id', $bonus->id)->first();

        $userBonus->amount_remaining_after_withdrawal = $userBonus->amount_remaining_after_withdrawal - $amount;
        $userBonus->save();
    }

    public function reverseAmountWithdrawn(Bonus $bonus, User $user, float $amount)
    {
        $userBonus = UserBonus::where('user_id', $user->id)
            ->where('bonus_id', $bonus->id)->first();

        $userBonus->amount_remaining_after_withdrawal = $userBonus->amount_remaining_after_withdrawal + $amount;
        $userBonus->save();
    }
}