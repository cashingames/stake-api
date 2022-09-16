<?php

namespace App\Console\Commands;

use App\Actions\SendPushNotification;
use App\Models\User;
use Illuminate\Console\Command;

class TriggerLiveTriviaNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'live-trivia:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger push notifications to users for live trivia';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        User::all()->map(function ($user) {
            (new SendPushNotification())->sendliveTriviaNotification($user);
        });
    }
}
