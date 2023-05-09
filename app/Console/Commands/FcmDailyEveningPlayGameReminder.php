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

class FcmDailyEveningPlayGameReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fcm:daily-evening-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily Evening Reminder to users to play game';

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
            "Evening GameArker",
            "How did your day go? Log in to GameArk to unwind.  Keep your brain sharp and have some fun in the process!🧠🌟"
        );
    }

    public function GetDailyReminderUserToken()
    {
        $allTokens = [];

        $devices = DB::select('SELECT DISTINCT device_token from fcm_push_subscriptions where valid = ?', [1]);
        foreach ($devices as $device) {
            $allTokens[] = $device->device_token;
        }

        return $allTokens;
    }
}
