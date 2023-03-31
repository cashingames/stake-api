<?php

namespace App\Console\Commands;

use App\Actions\SendPushNotification;
use App\Models\FcmPushSubscription;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TriggerSpecialHourOddsNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odds:special-hour';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger push notifications to users have average score lower than 4';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(SendPushNotification $pushNotification)
    {
        User::chunk(500, function($users) use($pushNotification){
            $allTokens = [];
            foreach ($users as $user){
                if ($user->gameSessions()->latest()->limit(3)->get()->avg('correct_count') < 5){
                    $device_token = FcmPushSubscription::where('user_id', $user->id)->latest()->first();

                    if($device_token != null){
                        $allTokens[] = $device_token->device_token;
                    }
                }
            }

            $pushNotification->sendSpecialHourOddsNotification($allTokens, true);
            Log::info("Attempting to send special hour notification to 500 users");
        });
        return 0;
    }
}
