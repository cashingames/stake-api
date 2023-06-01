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


 
}
