<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\UserPlan;
use App\Models\Plan;

class ExpireDailyBonusGames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonus:daily-expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivates all free games for the day';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $freePlan = Plan::where('is_free',true)->first();

        UserPlan::where('plan_id',$freePlan->id)
        ->whereNotNull('expire_at')
        ->update(['is_active'=> false]);
    }
}
