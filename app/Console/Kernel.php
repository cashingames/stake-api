<?php

namespace App\Console;

use App\Enums\FeatureFlags;
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
        Commands\RefundExpiredChallengeStakingAmount::class,
        Commands\SendInAppActivityUpdates::class,
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

        if (FeatureFlag::isEnabled(FeatureFlags::SEND_AUTOMATED_REPORTS)) {
            $schedule->command('daily-report:send')
                ->dailyAt('01:00');
            $schedule->command('weekly-report:send')
                ->dailyAt('01:00');
        }
        $schedule->command('challenge:staking-refund')->withoutOverlapping()->hourly();

        $schedule->command('bonus:hourly-refresh')->withoutOverlapping()->everyThreeHours();

        if (FeatureFlag::isEnabled(FeatureFlags::LIVE_TRIVIA_START_TIME_NOTIFICATION)) {
            $schedule->command('live-trivia:notify')->withoutOverlapping()->everyMinute();
        }

        if (FeatureFlag::isEnabled(FeatureFlags::IN_APP_ACTIVITIES_PUSH_NOTIFICATION)) {
            $schedule->command('boosts:send-notification')->withoutOverlapping()
                ->hourly()
                ->between('12:00', '14:00')
                ->days([0, 3]);
        }

        if (FeatureFlag::isEnabled(FeatureFlags::IN_APP_ACTIVITIES_PUSH_NOTIFICATION)) {
            $schedule->command('updates:send-notification')->withoutOverlapping()->hourly();
        }

        if (FeatureFlag::isEnabled(FeatureFlags::SPECIAL_HOUR_NOTIFICATION)) {
            $schedule->command('odds:special-hour')->withoutOverlapping()->hourly()->when(function () {

                $now = date("H") . ":00";
                $specialHours = config('odds.special_hours');

                return in_array($now, $specialHours);
            })->timezone('Africa/Lagos');
        }

        if (FeatureFlag::isEnabled(FeatureFlags::EXHIBITION_GAME_STAKING) or FeatureFlag::isEnabled(FeatureFlags::TRIVIA_GAME_STAKING)) {
            $schedule->command('winnings:credit')->withoutOverlapping()->hourly();
        }
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
