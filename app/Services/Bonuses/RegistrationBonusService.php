<?php

namespace App\Services\Bonuses;

use App\Models\Bonus;
use App\Enums\BonusType;
use App\Models\User;
use App\Repositories\Cashingames\BonusRepository;

class RegistrationBonusService implements BonusInterface
{
    private $bonusRepository;
    private $bonus;

    public function __construct()
    {
        $this->bonusRepository = new BonusRepository;
        $this->bonus = Bonus::where('name', BonusType::RegistrationBonus->value)->first();
    }

    public function giveBonus(User $user)
    {
       $this->bonusRepository->giveBonus($this->bonus, $user);
        
    }

    public function activateBonus()
    {
        
    }

    public function deactivateBonus()
    {
        
    }


}