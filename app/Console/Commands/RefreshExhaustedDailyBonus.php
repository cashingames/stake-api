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
    protected $signature = 'bonus:hourly-refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Exhausted Free Game Plan Hourly ';

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

        $this->renewExhaustedBonusToIncrement($today, $freePlan);
        $this->renewExhaustedBonusTillLimit($today, $freePlan);
    }

    public function renewExhaustedBonusToIncrement($today, $freePlan){
        User::all()->map(function ($user) use ($today, $freePlan) {
            $currentPlan = UserPlan::where('user_id', $user->id)
            ->where('plan_id', $freePlan->id)
            ->where('expire_at', $today)
            ->where('is_active', false)->first();

            if($currentPlan != null){
                // update
                UserPlan::where('id', $currentPlan->id)
                ->update([
                    'used_count' => 0,
                    'plan_count' => $this->incrementCount,
                    'is_active' => true,
                    'is_refreshing' => true
                ]);
            }
        });
    }
    public function renewExhaustedBonusTillLimit($today, $freePlan){
        User::all()->map(function ($user) use ($today, $freePlan) {
            $currentPlan = UserPlan::where('user_id', $user->id)
            ->where('plan_id', $freePlan->id)
            ->where('expire_at', $today)
            ->where('is_refreshing', true)->first();

            if($currentPlan != null){
                if($currentPlan->plan_count < $this->incrementCountLimit){
                    // update
                    UserPlan::where('id', $currentPlan->id)
                    ->update([
                        'plan_count' => $currentPlan->plan_count + $this->incrementCount,
                        'is_active' => true,
                        'is_refreshing' => true
                    ]);
                }else{
                    UserPlan::where('id', $currentPlan->id)
                    ->update([
                        'is_refreshing' => false
                    ]);
                }

            }
        });
    }
}
