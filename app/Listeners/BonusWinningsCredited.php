<?php

namespace App\Listeners;

use App\Events\CreditBonusWinnings;
use App\Repositories\Cashingames\WalletRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class BonusWinningsCredited
{
    /**
     * Create the event listener.
     */
    public function __construct(private readonly WalletRepository $walletRepository)
    {
      
    }

    /**
     * Handle the event.
     */
    public function handle(CreditBonusWinnings $event): void
    {
        $this->walletRepository->credit($event->user->wallet, $event->amount, 'Bonus Winnings Credited', null);
    }
}
