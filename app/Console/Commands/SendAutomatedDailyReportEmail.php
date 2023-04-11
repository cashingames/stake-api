<?php

namespace App\Console\Commands;

use App\Mail\DailyReportEmail;
use App\Repositories\Cashingames\ChallengeReportsRepository;
use App\Services\AutomatedReportsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use League\CommonMark\Parser\Inline\NewlineParser;

class SendAutomatedDailyReportEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily-report:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sends daily report of platform metrics';

    /**
     * Execute the console command.
     *
     * @return int
     */


    public function handle(): void
    {   
        $challengeRepository = new ChallengeReportsRepository();

        $reportsService = new AutomatedReportsService($challengeRepository);

        $data = $reportsService->getDailyReports();

        Mail::to(config('app.admin_email'))->send(new DailyReportEmail($data));
    }
}
