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
    
        $this->bonusService->deactivateBonus($event->user);
    }
}
