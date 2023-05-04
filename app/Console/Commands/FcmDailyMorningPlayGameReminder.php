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
use App\Enums\ClientPlatform;

class FcmDailyMorningPlayGameReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fcm:daily-morning-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily Morning Reminder to users to play game';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pushNotification = new SendPushNotification(ClientPlatform::GameArkMobile);
        $pushTokens = $this->GetDailyReminderUserToken();

        // send
        $pushNotification->sendDailyReminderNotification(
            $pushTokens,
            true,
            "Morning GameArker",
            "Ready to put your knowledge to the test? Don't miss out on today's trivia challenge in GameArk! Log in now and see how you stack up against other players.");
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
