<?php

namespace App\Console\Commands;

use App\Actions\GetActiveUsersDeviceTokensAction;
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
    public function handle(GetActiveUsersDeviceTokensAction $getActiveUsersDeviceTokensAction)
    {
        $pushNotification = new SendPushNotification(ClientPlatform::GameArkMobile);
        $pushTokens = $this->GetDailyReminderUserToken($getActiveUsersDeviceTokensAction);

        // send
        $pushNotification->sendDailyReminderNotification(
            $pushTokens,
            true,
            "Evening GameArker",
            "How did your day go? Log in to GameArk to unwind.  Keep your brain sharp and have some fun in the process!🧠🌟");
    }

    public function GetDailyReminderUserToken(GetActiveUsersDeviceTokensAction $getActiveUsersDeviceTokensAction)
    {
        $allTokens = [];
        $twoWeeksAgo = now()->subDays(14);

        $devices = $getActiveUsersDeviceTokensAction->execute($twoWeeksAgo);
        foreach ($devices as $device) {
            $allTokens[] = $device->device_token;
        }

        return $allTokens;
    }

}
