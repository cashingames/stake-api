<?php

namespace App\Console\Commands;

use App\Models\DailyObjective;
use App\Models\Objective;
use App\Models\UserDailyObjective;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpDateDailyObjective extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:up-date-daily-objective';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update User daily objectives';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        // Get the IDs of records selected on the previous day
        // $previousSelections = DailyObjective::pluck('objective_id')->toArray();
        $previousDay = Carbon::now()->subday();
        $previousSelections = DailyObjective::whereBetween('day',[now()->startOfDay(), $previousDay] )->pluck('objective_id')->toArray();

        // Select random objectives excluding those from the previous day
        $objectives = Objective::whereNotIn('id', $previousSelections)
            ->inRandomOrder()
            ->take(2)
            ->get();

            UserDailyObjective::whereDate('created_at', '<', now()->startOfDay())->delete();
        $data = [];

        foreach ($objectives as $objective) {
            $data[] = [
                "objective_id" => $objective->id,
                "day" => now(),
                "milestone_count" => $objective->milestone_count,
                "created_at" => now(),
                "updated_at" => now()
            ];
        }

        if (count($data) > 0) {
            DB::table('daily_objectives')->insert($data);
        }

        return $data;
    }
}
