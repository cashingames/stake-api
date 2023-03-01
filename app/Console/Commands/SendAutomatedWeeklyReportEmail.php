<?php

namespace App\Console\Commands;

use App\Mail\WeeklyReportEmail;
use App\Services\AutomatedReportsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAutomatedWeeklyReportEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weekly-report:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sends weekly report of platform metrics';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $reportsService = new AutomatedReportsService();

        $data = $reportsService->getWeeklyReports();

        Mail::to(config('app.admin_email'))->send(new WeeklyReportEmail($data));
    }
}
