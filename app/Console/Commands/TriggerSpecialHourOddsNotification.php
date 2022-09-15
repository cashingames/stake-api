<?php

namespace App\Console\Commands;

use App\Actions\SendPushNotification;
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
    public function handle()
    {
        User::has('gameSessions', '>=', 3)->chunk(500, function($users){
            foreach ($users as $user){
                if ($user->gameSessions()->latest()->limit(3)->get()->avg('correct_count') < 5){
                    (new SendPushNotification())->sendSpecialHourOddsNotification($user);
                }
            }
            Log::info("Attempting to send special hour notification to 500 users");
        });
        return 0;
    }
}
