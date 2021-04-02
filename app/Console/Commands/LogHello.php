<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LogHello extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:hello';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Log a greeting to the console';

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
       echo("Hello , I'm an artisan command!");
    }
}
