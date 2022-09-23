<?php

namespace App\Console\Commands;

use App\Actions\SendPushNotification;
use App\Models\FcmPushSubscription;
use App\Models\LiveTrivia;
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
            FcmPushSubscription::chunk(500, function($devices){
                foreach ($devices as $device){
                    (new SendPushNotification())->sendliveTriviaNotification($device, "in 1 hour");
                }
                Log::info("Attempting to send live trivia notification to 500 devices");
            });
        }
        if ($currentTime->diffInMinutes($liveTriviaStartTime, false) == 30){
            $this->info("30 minutes away");
            Log::info("30 minutes away at " . $liveTriviaStartTime);
            FcmPushSubscription::chunk(500, function($devices){
                foreach ($devices as $device){
                    (new SendPushNotification())->sendliveTriviaNotification($device, "in 30 minutes");
                }
                Log::info("Attempting to send live trivia notification to 500 devices");
            });
        }
        
        if ($currentTime->diffInMinutes($liveTriviaStartTime) == 0){
            $this->info("Right on time");
            Log::info("Right on time " . $liveTriviaStartTime);
            FcmPushSubscription::chunk(500, function($devices){
                foreach ($devices as $device){
                    (new SendPushNotification())->sendliveTriviaNotification($device, "now");
                }
                Log::info("Attempting to send live trivia notification to 500 devices");
            });
        }
    }
}
