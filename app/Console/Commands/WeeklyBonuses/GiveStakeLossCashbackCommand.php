<?php

namespace App\Console\Commands\WeeklyBonuses;

use App\Actions\Bonus\GiveStakeLossCashbackAction;
use Illuminate\Console\Command;

class GiveStakeLossCashbackCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonus:stake:loss-cashback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give weekly loss cashback';

    /**
     * Execute the console command.
     */
    public function handle(GiveStakeLossCashbackAction $action)
    {
        $action->execute();
    }
}
