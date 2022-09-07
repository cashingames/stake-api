<?php

namespace App\Console;

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
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('bonus:daily-expire')
            ->dailyAt('00:01');
        $schedule->command('bonus:daily-activate')
            ->dailyAt('00:03');

        $schedule->command('odds:special-hour')->hourly()->when(function () {

            $now = date("H") . ":00";
            $specialHours = config('odds.special_hours');

            return in_array($now, $specialHours);
        })->timezone('Africa/Lagos');

        $schedule->command('winnings:credit')
            ->hourly();
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
