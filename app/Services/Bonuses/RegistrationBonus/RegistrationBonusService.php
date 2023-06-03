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

    public function activateBonus(User $user, float $amount)
    {
        $inactiveBonus = $this->inactiveRegistrationBonus($user);
        if (!is_null($inactiveBonus)) {
            $this->bonusRepository->activateBonus($this->bonus, $user, $amount);
            return true;
        }
        return false;
    }

    public function deactivateBonus()
    {
    }

    public function inactiveRegistrationBonus($user)
    {
        return $this->userBonusQuery($user)
            ->where('is_on', false)->first();
    }

    public function hasActiveRegistrationBonus($user)
    {
        return $this->userBonusQuery($user)
            ->where('is_on', true)->exists();
    }

    public function activeRegistrationBonus($user)
    {
        return $this->userBonusQuery($user)
            ->where('is_on', true)->first();
    }

    public function hasPlayedCategory($user, $category)
    {
        return $user->gameSessions()->where('category_id', $category)->exists();
    }

    public function updateAmountWon($user, $amount){
        $this->bonusRepository->updateWonAmount($this->bonus, $user, $amount);
    }
    
    private function userBonusQuery($user)
    {
        return UserBonus::where('user_id', $user->id)
            ->where('bonus_id', $this->bonus->id);
    }
}
