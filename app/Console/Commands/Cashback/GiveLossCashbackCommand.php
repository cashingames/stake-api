<?php

namespace App\Console\Commands\Cashback;

use App\Actions\Bonus\GiveLossCashbackAction;
use Illuminate\Console\Command;

class GiveLossCashbackCommand extends Command
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
    protected $description = 'Give loss cashback';

    /**
     * Execute the console command.
     */
    public function handle(GiveLossCashbackAction $action)
    {
        $action->execute();
    }
}