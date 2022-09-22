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
        $this->info("called at " . now());
        Log::info("called at " . now());
        $activeLiveTrivia = LiveTrivia::active()->first();
      
        if (is_null($activeLiveTrivia)){
            return 0;
        }

        $currentTime = now();
        $liveTriviaStartTime = Carbon::parse($activeLiveTrivia->start_time);

        if ($currentTime->diffInMinutes($liveTriviaStartTime, false) == 60){
            $this->info("1 hour away");
            Log::info("1 hour away at " . $liveTriviaStartTime);
            User::chunk(500, function($users){
                foreach ($users as $user){
                    (new SendPushNotification())->sendliveTriviaNotification($user, "in 1 hour");
                }
                Log::info("Attempting to send live trivia notification to 500 users");
            });
        }
        if ($currentTime->diffInMinutes($liveTriviaStartTime, false) == 30){
            $this->info("30 minutes away");
            Log::info("30 minutes away at " . $liveTriviaStartTime);
            User::chunk(500, function($users){
                foreach ($users as $user){
                    (new SendPushNotification())->sendliveTriviaNotification($user, "in 30 minutes");
                }
                Log::info("Attempting to send live trivia notification to 500 users");
            });
        }
        
        if ($currentTime->diffInMinutes($liveTriviaStartTime) == 0){
            $this->info("Right on time");
            Log::info("Right on time " . $liveTriviaStartTime);
            User::chunk(500, function($users){
                foreach ($users as $user){
                    (new SendPushNotification())->sendliveTriviaNotification($user, "now");
                }
                Log::info("Attempting to send live trivia notification to 500 users");
            });
        }
    }
}
