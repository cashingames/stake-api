<?php

namespace App\Console\Commands;

use App\Actions\SendPushNotification;
use App\Enums\ClientPlatform;
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

class InActiveUserReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fcm:inactive-user-reminder';

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
    public function handle()
    {
        $pushNotification = new SendPushNotification(ClientPlatform::GameArkMobile);
        $date=new datetime();
        $lastsevendaysdate= date_sub($date,date_interval_create_from_date_string("7 days"));
        $lastthirtydaysdate= date_sub($date,date_interval_create_from_date_string("30 days"));

        $data = DB::select("SELECT b.device_token FROM users AS a INNER JOIN fcm_push_subscriptions AS b ON a.id=b.user_id WHERE a.last_activity_time >= ? AND a.last_activity_time <= ?", [$lastsevendaysdate, $lastthirtydaysdate]);

        $pushNotification->sendDailyReminderNotification(
            $data,
            true,
            "GameArk",
            "Hey there! We've missed you in GameArk! Don't let your adventure come to an end. Log back in and continue your journey through our immersive world.!🎉🎮");

    }

}
