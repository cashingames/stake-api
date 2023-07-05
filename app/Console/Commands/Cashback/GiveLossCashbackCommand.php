<?php

namespace App\Console\Commands\Cashback;

use Illuminate\Console\Command;
use App\Actions\Bonus\GiveLossCashbackAction;
use App\Enums\Bonus\CashbackAccrualDuration;

class GiveLossCashbackCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonus:stake:loss-cashback {--duration=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give loss cashback';

    /**
     * Execute the console command.
     */
    public function handle(GiveLossCashbackAction $action)
    {
        $duration = $this->option('duration');
        $action->execute(CashbackAccrualDuration::tryFrom($duration) ?? CashbackAccrualDuration::DAILY);
    }
}