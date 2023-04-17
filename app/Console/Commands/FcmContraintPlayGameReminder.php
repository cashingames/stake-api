<?php

namespace App\Console\Commands;

use App\Actions\SendPushNotification;
use App\Enums\FeatureFlags;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\UserPlan;
use App\Models\User;
use App\Models\Plan;
use App\Services\FeatureFlag;
use Illuminate\Support\Facades\DB;
use App\Models\FcmPushSubscription;

use DateTime;

class FcmContraintPlayGameReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fcm:contraint-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remember Users to play game after awhile of in-activity';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(SendPushNotification $pushNotification)
    {
        $pushTokens = [];
        User::all()->map(function ($user) use ($pushTokens) {
            $lastActive = new DateTime($user->last_activity_time);
            $current = new DateTime();

            $interval = $lastActive->diff($current);
            if($interval->days == 7){
                // meaning it's 7 days since he played last
                $pushTokens[] = $this->getToken($user);
            }else if($interval->days == 30){
                // meaning it's 7 days since he played last
                $pushTokens[] = $this->getToken($user);
            }
        });

        $pushNotification->sendDailyReminderNotification(
            $pushTokens,
            true,
            "GameArk",
            "Hey there! We've missed you in GameArk! Don't let your adventure come to an end. Log back in and continue your journey through our immersive world.!🎉🎮");

    }

    public function getToken($user)
    {
        $device_token = FcmPushSubscription::where('user_id', $user->id)->latest()->first();
        if (!is_null($device_token)) {
            return $device_token->device_token;
        }
        return "";
    }
}
