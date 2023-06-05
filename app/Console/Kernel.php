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
        // Commands\CreditWinnings::class,
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

        if (FeatureFlag::isEnabled(FeatureFlags::SEND_AUTOMATED_REPORTS)) {
            $schedule->command('daily-report:send')
                ->dailyAt('01:00');
            $schedule->command('weekly-report:send')
                ->dailyAt('01:00');
        }

        if (FeatureFlag::isEnabled(FeatureFlags::REGISTRATION_BONUS)) {
            $schedule->command('registration-bonus:expire')->daily();
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
