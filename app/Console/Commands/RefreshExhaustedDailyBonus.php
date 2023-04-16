<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\UserPlan;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

class RefreshExhaustedDailyBonus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonus:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Exhausted Free Game Plan ';

    public $incrementCount = 5;
    public $incrementCountLimit = 15;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::now()->endOfDay();
        $freePlan = Plan::where('is_free', true)->first();

        $this->renewExhaustedBonusToIncrementLimit($today, $freePlan);
        // $this->renewExhaustedBonusTillLimit($today, $freePlan);
    }

    public function renewExhaustedBonusToIncrementLimit($today, $freePlan){
        User::all()->map(function ($user) use ($today, $freePlan) {

            if(!($user->hasActiveFreePlan())){
                UserPlan::create([
                    'plan_id' => $freePlan->id,
                    'user_id' => $user->id,
                    'description' => "Refreshing daily plan for " . $user->username,
                    'used_count' => 0,
                    'plan_count' => 5,
                    'is_active' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'expire_at' => Carbon::now()->endOfDay()
                ]);
            }
        });
    }
}
