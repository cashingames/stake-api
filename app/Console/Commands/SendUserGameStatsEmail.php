<?php

namespace App\Console\Commands;

use App\Mail\UserGameStatsEmail;
use App\Models\User;
use App\Services\UserGameStatsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendUserGameStatsEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-user-game-stats-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userGameReport = new UserGameStatsService();
        $users = User::all();
        foreach ($users as $user) {
            $data = $userGameReport->getBiWeeklyUserGameStats($user);
            Mail::to($user->email)->send(new UserGameStatsEmail($data));
            $this->info('User Game Stats email sent to test address successfully.');
        }
    }
}
