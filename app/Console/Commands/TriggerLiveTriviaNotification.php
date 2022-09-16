<?php

namespace App\Console\Commands;

use App\Actions\SendPushNotification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TriggerLiveTriviaNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        User::chunk(500, function($users){
            foreach ($users as $user){
                (new SendPushNotification())->sendliveTriviaNotification($user);
            }
            Log::info("Attempting to send live trivia notification to 500 users");
        });
    }
}
