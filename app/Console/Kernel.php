<?php

namespace App\Console;

use App\Enums\FeatureFlags;
use App\Jobs\ReactivateUserReward;
use App\Services\FeatureFlag;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

// use App\Console\Commands\TriviaStaking\Analytics\ComputeUsersLevelsCommand;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        Commands\ExpireDailyBonusGames::class,
        Commands\GiveDailyBonusGames::class,
        Commands\CreditWinnings::class,
        Commands\SendUserGameStatsEmail::class
        // Commands\SendInAppActivityUpdates::class,
        // ComputeUsersLevelsCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command("queue:work --tries=1 --stop-when-empty")->everyMinute();

        // $schedule->command(ComputeUsersLevelsCommand::class)->everyMinute();
        $schedule->command('bonus:daily-expire')->withoutOverlapping()
            ->dailyAt('00:01');
        $schedule->command('bonus:daily-activate')->withoutOverlapping()
            ->dailyAt('00:03');

        // if (FeatureFlag::isEnabled(FeatureFlags::SEND_AUTOMATED_REPORTS)) {
        //     $schedule->command('daily-report:send')
        //         ->dailyAt('01:00');
        //     $schedule->command('weekly-report:send')
        //         ->dailyAt('01:00');
        // }

        $schedule->command('bonus:refresh')->withoutOverlapping()->everyThreeHours();
        // fcm:contraint-reminder
        // $schedule->command('fcm:daily-morning-reminder')->withoutOverlapping()->dailyAt('08:00');
        // $schedule->command('fcm:daily-afternoon-reminder')->withoutOverlapping()->dailyAt('13:00');
        // $schedule->command('fcm:daily-evening-reminder')->withoutOverlapping()->dailyAt('18:00');
        // $schedule->command('fcm:inactive-user-reminder')->withoutOverlapping()->weekly('13:00');
        $schedule->job(ReactivateUserReward::class)->dailyAt('00:05');
        $schedule->command('app:send-user-game-stats-email')->when(function () {
            return now()->weekOfYear % 2 == 0;
        });
        


        // if (FeatureFlag::isEnabled(FeatureFlags::LIVE_TRIVIA_START_TIME_NOTIFICATION)) {
        //     $schedule->command('live-trivia:notify')->withoutOverlapping()->everyMinute();
        // }
        //$schedule->command('challenge-requests:clean-up')->withoutOverlapping()->everyMinute();

        // if (FeatureFlag::isEnabled(FeatureFlags::LIVE_TRIVIA_START_TIME_NOTIFICATION)) {
        //     $schedule->command('live-trivia:notify')->withoutOverlapping()->everyMinute();
        // }

        // if (FeatureFlag::isEnabled(FeatureFlags::IN_APP_ACTIVITIES_PUSH_NOTIFICATION)) {
        //     $schedule->command('boosts:send-notification')->withoutOverlapping()
        //         ->hourly()
        //         ->between('12:00', '14:00')
        //         ->days([0, 3]);
        // }

        // if (FeatureFlag::isEnabled(FeatureFlags::IN_APP_ACTIVITIES_PUSH_NOTIFICATION)) {
        //     $schedule->command('updates:send-notification')->withoutOverlapping()->hourly();
        // }

        // if (FeatureFlag::isEnabled(FeatureFlags::SPECIAL_HOUR_NOTIFICATION)) {
        //     $schedule->command('odds:special-hour')->withoutOverlapping()->hourly()->when(function () {

        //         $now = date("H") . ":00";
        //         $specialHours = config('odds.special_hours');

        //         return in_array($now, $specialHours);
        //     })->timezone('Africa/Lagos');
        // }

        //This command matures users winnings based on their viable dates
        // if (FeatureFlag::isEnabled(FeatureFlags::EXHIBITION_GAME_STAKING) or FeatureFlag::isEnabled(FeatureFlags::TRIVIA_GAME_STAKING)) {
        //     $schedule->command('winnings:credit')->withoutOverlapping()->hourly();
        // }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
