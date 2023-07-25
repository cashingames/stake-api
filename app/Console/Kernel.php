<?php

namespace App\Console;

use App\Console\Commands\RegistrationBonus\ExpireBonusCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

//#[CodeCoverageIgnore]
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
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
        // $schedule->command(ExpireBonusCommand::class)->daily();
        // $schedule->command(GiveLossCashbackCommand::class, [
        //     'duration' => CashbackAccrualDuration::DAILY->value
        // ])->daily();
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