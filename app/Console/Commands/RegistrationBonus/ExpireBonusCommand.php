<?php

namespace App\Console\Commands\RegistrationBonus;

use \App\Actions\Bonus\ExpireRegistrationBonusAction;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class ExpireBonusCommand extends Command implements Isolatable
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonus:registration:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire due registration bonus registration bonuses';

    /**
     * Execute the console command.
     */
    public function handle(ExpireRegistrationBonusAction $expireRegistrationBonusAction)
    {
        $expireRegistrationBonusAction->execute();
    }
}
