<?php

namespace App\Services\Bonuses\RegistrationBonus;

use App\Models\Bonus;
use App\Enums\BonusType;
use App\Enums\WalletTransactionAction;
use App\Models\User;
use App\Models\UserBonus;
use App\Models\WalletTransaction;
use App\Repositories\Cashingames\BonusRepository;
use App\Services\Bonuses\BonusInterface;

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

    public function activateBonus(User $user)
    {
        $inactiveBonus = $this->inactiveRegistrationBonus($user);
        if (!is_null($inactiveBonus)) {
            $this->bonusRepository->activateBonus($this->bonus, $user);
            return true;
        }
        return false;
    }

    public function deactivateBonus()
    {
    }

    private function inactiveRegistrationBonus($user)
    {
        return UserBonus::where('user_id', $user->id)
            ->where('bonus_id', $this->bonus->id)
            ->where('is_on', false)->first();
    }
}
