<?php

namespace App\Services\Bonuses;

use App\Models\User;

interface BonusInterface
{
    public function giveBonus(User $user);

    public function activateBonus();

    public function deactivateBonus();
}