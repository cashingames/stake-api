<?php

namespace App\Console\Commands;

use App\Actions\SendPushNotification;
use App\Enums\FeatureFlags;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\UserPlan;
use App\Models\User;
use App\Models\Plan;
use App\Services\FeatureFlag;
use Illuminate\Support\Facades\DB;

class GiveDailyBonusGames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonus:daily-activate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gives daily bonus games';

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
        $freePlan = Plan::where('is_free', true)->first();

        User::all()->map(function ($user) use ($freePlan) {

            UserPlan::create([
                'plan_id' => $freePlan->id,
                'user_id' => $user->id,
                'description' => "Daily bonus plan for " . $user->username,
                'used_count' => 0,
                'plan_count' => 5,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'expire_at' => Carbon::now()->endOfDay()
            ]);
        });
        if (FeatureFlag::isEnabled(FeatureFlags::IN_APP_ACTIVITIES_PUSH_NOTIFICATION)) {
            DB::table('fcm_push_subscriptions')->latest()->distinct()->chunk(500, function ($devices) {
                foreach ($devices as $device) {
                    (new SendPushNotification(null))->sendDailyBonusGamesNotification($device);
                }
            });
        }
    }
}
