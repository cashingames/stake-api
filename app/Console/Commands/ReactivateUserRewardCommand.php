<?php

namespace App\Console\Commands;

use App\Models\UserReward;
use Illuminate\Console\Command;

class ReactivateUserRewardCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user-reward:reactivate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command reactivates missed user reward';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        UserReward::where('reward_count', -1)
        ->update(['reward_count' => 0, 'reward_milestone' => 1]);
    }
}
