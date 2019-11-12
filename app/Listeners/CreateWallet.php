<?php

namespace App\Listeners;

use App\Wallet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\Registered;

class CreateWallet
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        //
        Wallet::create([
            'user_id' => $event->user->id,
            'bonus' => 0,
            'cash' => 0,
            'balance' => 0
        ]);
    }
}
