<?php

namespace App\Console\Commands;

use App\Actions\SendPushNotification;
use App\Models\User;
use App\Models\FcmPushSubscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBoostsAndGamesReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boosts:send-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications for games and boosts';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(SendPushNotification $pushNotification)
    {
        User::chunk(500, function($users) use ($pushNotification){
            $allTokens = [];
            foreach ($users as $user){
                $device_token = FcmPushSubscription::where('user_id', $user->id)->latest()->first();
                if (!is_null($device_token)) {
                    $allTokens[] = $device_token->device_token;
                }

                // $pushNotification->sendBoostsReminderNotification($user);
            }
            $pushNotification->sendBoostsReminderNotification($allTokens, true);
            Log::info("Attempting to send boosts notification to 500 users");
        });
    }
}
