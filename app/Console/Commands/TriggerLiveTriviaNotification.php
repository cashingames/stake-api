<?php

namespace App\Console\Commands;

use App\Actions\SendPushNotification;
use App\Models\LiveTrivia;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class TriggerLiveTriviaNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'live-trivia:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger live trivia notifications to users';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {   
        $activeLiveTrivia = LiveTrivia::active()->first();
      
        if (is_null($activeLiveTrivia)){
            return 0;
        }

        $currentTime = now();
        $liveTriviaStartTime = Carbon::parse($activeLiveTrivia->start_time);

        if ($liveTriviaStartTime->diffInHours($currentTime) == 1){
            User::chunk(500, function($users){
                foreach ($users as $user){
                    (new SendPushNotification())->sendliveTriviaNotification($user, "1 hour");
                }
                Log::info("Attempting to send live trivia notification to 500 users");
            });
        }
        if ($liveTriviaStartTime->diffInMinutes($currentTime) == 30){
            User::chunk(500, function($users){
                foreach ($users as $user){
                    (new SendPushNotification())->sendliveTriviaNotification($user, "30 minutes");
                }
                Log::info("Attempting to send live trivia notification to 500 users");
            });
        }
        
        if ($liveTriviaStartTime->diffInMinutes($currentTime) == 1){
            User::chunk(500, function($users){
                foreach ($users as $user){
                    (new SendPushNotification())->sendliveTriviaNotification($user, "1 minute");
                }
                Log::info("Attempting to send live trivia notification to 500 users");
            });
        }
    }
}
