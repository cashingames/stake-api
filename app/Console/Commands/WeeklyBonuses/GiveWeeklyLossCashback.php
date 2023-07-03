<?php

namespace App\Console\Commands\WeeklyBonuses;

use App\Actions\Bonus\GiveWeeklyLossCashback as WeeklyLossAction;
use Illuminate\Console\Command;

class GiveWeeklyLossCashback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weekly-loss:cashback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give weekly loss cashback';

    /**
     * Execute the console command.
     */
    public function handle(WeeklyLossAction $weeklyLossAction)
    {
        $weeklyLossAction->execute();
    }
}
