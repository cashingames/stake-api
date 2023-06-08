<?php

namespace App\Listeners;

use App\Enums\WalletTransactionAction;
use App\Events\CreditRegistrationBonusWinnings;
use App\Repositories\Cashingames\WalletRepository;
use App\Services\Bonuses\RegistrationBonus\RegistrationBonusService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RegistrationBonusWinningsCredited
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly WalletRepository $walletRepository,
        private readonly RegistrationBonusService $bonusService
    ) {
    }

    /**
     * Handle the event.
     */
    public function handle(CreditRegistrationBonusWinnings $event): void
    {   
        //credit users withdrawable
        $this->walletRepository->credit(
            $event->user->wallet,
            $event->amount,
            'Bonus Winnings Credited',
            null
        );
        //debit users bonus of the turnover
        $this->walletRepository->debit(
            $event->user->wallet,
            $this->bonusService->activeRegistrationBonus($event->user)->total_amount_won,
            'Bonus Turnover Migrated To Winnings',
            null,
            'bonus',
            WalletTransactionAction::BonusTurnoverMigrated->value
        );
        //deactivate registration bonus
        $this->bonusService->deactivateBonus($event->user);
    }
}
