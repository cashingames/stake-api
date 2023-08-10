<?php

namespace App\Console\Commands;

use App\Actions\SendPushNotification;
use App\Enums\ClientPlatform;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
            "How did your day go? Log in to GameArk to unwind.  Keep your brain sharp and have some fun in the process!🧠🌟");
    }

    public function GetDailyReminderUserToken()
    {
        $allTokens = [];
        $twoWeeksAgo = now()->subDays(14);

        $devices = DB::select('SELECT DISTINCT device_token
        FROM fcm_push_subscriptions AS fcm
        JOIN users ON fcm.user_id = users.id
        WHERE fcm.valid = ? AND users.last_activity_time >= ?', [1, $twoWeeksAgo]);
        foreach ($devices as $device) {
            $allTokens[] = $device->device_token;
        }

        return $allTokens;
    }

}
