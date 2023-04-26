<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\UserPlan;
use App\Models\User;
use App\Models\Plan;
use App\Models\FcmPushSubscription;
use Illuminate\Support\Facades\DB;
use App\Actions\SendPushNotification;

class FcmDailyAfternoonPlayGameReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fcm:daily-afternoon-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily Afternoon Reminder to users to play game';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(SendPushNotification $pushNotification)
    {
        $pushTokens = $this->GetDailyReminderUserToken();

        // send
        $pushNotification->sendDailyReminderNotification(
            $pushTokens,
            true,
            "Afternoon GameArker",
            "Get ready for today's adventure in GameArk! Your daily game lives and boosts have been added and are ready to use. Log in now and let's make today's adventure the best one yet!🚀🎮");
    }

    public function GetDailyReminderUserToken()
    {
        $allTokens = [];

        $devices = DB::select('SELECT device_token from fcm_push_subscriptions where valid = ?', [1]);
        foreach ($devices as $device) {
            $allTokens[] = $device->device_token;
        }

        return $allTokens;
    }

}
