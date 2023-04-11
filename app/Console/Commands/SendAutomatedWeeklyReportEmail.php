<?php

namespace App\Console\Commands;

use App\Mail\WeeklyReportEmail;
use App\Repositories\Cashingames\ChallengeReportsRepository;
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
    public function handle() : void
    {   
        $challengeRepository = new ChallengeReportsRepository();

        $reportsService = new AutomatedReportsService($challengeRepository);

        $data = $reportsService->getWeeklyReports();

        Mail::to(config('app.admin_email'))->send(new WeeklyReportEmail($data));
    }
}
