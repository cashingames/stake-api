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
        Commands\SendUserGameStatsEmail::class
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
        $schedule->command('bonus:daily-expire')->withoutOverlapping()
            ->dailyAt('00:01');
        $schedule->command('bonus:daily-activate')->withoutOverlapping()
            ->dailyAt('00:03');


        $schedule->command('bonus:refresh')->withoutOverlapping()->everyThreeHours();
        // fcm:contraint-reminder
        $schedule->command('fcm:daily-morning-reminder')->withoutOverlapping()->dailyAt('08:00');
        $schedule->command('fcm:daily-afternoon-reminder')->withoutOverlapping()->dailyAt('13:00');
        $schedule->command('fcm:daily-evening-reminder')->withoutOverlapping()->dailyAt('18:00');
        $schedule->command('fcm:inactive-user-reminder')->withoutOverlapping()->weekly('13:00');
        $schedule->job(ReactivateUserReward::class)->dailyAt('00:05');
        
        $schedule->command('app:send-user-game-stats-email')->weekly()->mondays()
        ->when(function () {
            return date('W') % 2;
         })->at("13:38");

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
