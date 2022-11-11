<?php

namespace App\Console\Commands;

use App\Actions\SendPushNotification;
use App\Models\User;
use App\Notifications\InAppActivityNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendInAppActivityUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updates:send-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send in-app activity updates to users';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        User::chunk(500, function($users){
            foreach ($users as $user){
                $activities = json_decode(file_get_contents(storage_path() . "/appActivities.json"), true);
                $key = array_rand($activities);
               
                $user->notify(new InAppActivityNotification($activities[$key]["Message"]));
                (new SendPushNotification())->sendInAppActivityNotification($user, $activities[$key]["Message"]);
                
            }
            Log::info("Attempting to send app activity updates to 500 users");
        });
    }
}
