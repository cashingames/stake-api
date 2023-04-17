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

class FcmContraintPlayGameReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fcm:contraint-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remember Users to play game after awhile of in-activity';

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
    public function handle(SendPushNotification $pushNotification)
    {
        $pushTokens = [];
        User::all()->map(function ($user) use ($freePlan) {
            $lastActive = new DateTime($user->last_activity_time);
            $current = new DateTime();

            $interval = $lastActive->diff($current);
            if($interval->days == 7){
                // meaning it's 7 days since he played last
            }
        });

        if (FeatureFlag::isEnabled(FeatureFlags::IN_APP_ACTIVITIES_PUSH_NOTIFICATION)) {
            DB::table('fcm_push_subscriptions')->latest()->distinct()->chunk(500, function ($devices) use($pushNotification){
                $allTokens = [];
                foreach ($devices as $device) {
                    $allTokens[] = $device->device_token;
                }
                $pushNotification->sendDailyBonusGamesNotification($allTokens, true);
            });
        }
    }
}
