<?php

namespace App\Services\Bonuses;

use App\Models\User;

interface BonusInterface
{
    public function giveBonus(User $user);

    public function activateBonus(User $user);

    public function deactivateBonus();

}