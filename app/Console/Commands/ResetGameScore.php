<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class ResetGameScore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scores:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets Game Scores';

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
        DB::table('games')
              ->where('points_gained','>', 0)
              ->update(['points_gained' => 0]);
    }
}
