<?php

namespace App\Repositories\Cashingames;

use App\Models\Bonus;
use App\Models\User;
use App\Models\UserBonus;
use App\Models\Wallet;

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
        $userBonus = UserBonus::where('user_id', $user->id)
        ->where('bonus_id', $bonus->id)
        ->where('is_on', true)->first();
        
        $wallet = Wallet::where('user_id',$user->id)->first();
      
        $wallet->bonus = $wallet->bonus - $userBonus->amount_remaining_after_staking;
        $wallet->save();
        
        $userBonus->update([
            'is_on' => false
        ]);
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
