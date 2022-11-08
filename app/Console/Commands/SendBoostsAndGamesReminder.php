<?php

namespace App\Console\Commands;

use App\Actions\SendPushNotification;
use App\Models\User;
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
    public function handle()
    {
        User::chunk(500, function($users){
            foreach ($users as $user){
                (new SendPushNotification())->sendBoostsReminderNotification($user);
                
            }
            Log::info("Attempting to send boosts notification to 500 users");
        });
    }
}
