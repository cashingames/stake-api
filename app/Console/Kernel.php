<?php

namespace App\Console;

use App\Enums\FeatureFlags;
use App\Services\FeatureFlag;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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

        $schedule->command('bonus:daily-expire')
            ->dailyAt('00:01');
        $schedule->command('bonus:daily-activate')
            ->dailyAt('00:03');

        if (FeatureFlag::isEnabled(FeatureFlags::LIVE_TRIVIA_START_TIME_NOTIFICATION)) {
            $schedule->command('live-trivia:notify')->everyMinute();
        }

        if (FeatureFlag::isEnabled(FeatureFlags::SPECIAL_HOUR_NOTIFICATION)) {
            $schedule->command('odds:special-hour')->hourly()->when(function () {

                $now = date("H") . ":00";
                $specialHours = config('odds.special_hours');

                return in_array($now, $specialHours);
            })->timezone('Africa/Lagos');
        }
        $schedule->command('challenge:staking-refund')->everyMinute();

        if (FeatureFlag::isEnabled(FeatureFlags::EXHIBITION_GAME_STAKING) or FeatureFlag::isEnabled(FeatureFlags::TRIVIA_GAME_STAKING)) {
            $schedule->command('winnings:credit')
                ->hourly();
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
