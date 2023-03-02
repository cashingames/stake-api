<?php

namespace Tests\Unit;

use App\Models\ExhibitionStaking;
use App\Models\GameSession;
use App\Models\Staking;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\AutomatedReportsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AutomatedReportsServiceTest extends TestCase
{
    use RefreshDatabase;

    public $reportsService;
    public $user;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()
            ->count(10)
            ->create();
        WalletTransaction::factory()
            ->count(10)
            ->create();
        Staking::factory()
            ->count(10)
            ->create();
        $this->user = User::inRandomOrder()->first();
        $this->reportsService = new AutomatedReportsService();
    }

    public function test_that_daily_reports_returns_data()
    {
        $dailyReports = $this->reportsService->getDailyReports();
        $this->assertCount(5, $dailyReports);
    }

    public function test_that_daily_reports_returns_net_profit()
    {
        $dailyReports = $this->reportsService->getDailyReports();
        $this->assertArrayHasKey('netProfit', $dailyReports);
    }

    public function test_that_daily_reports_correct_total_withdrawal_amount()
    {
        WalletTransaction::query()->update([
            'transaction_type' => 'DEBIT',
            'description' => 'Winnings Withdrawal Made',
            'created_at' => now(),
            'amount' => 100
        ]);

        $dailyReports = $this->reportsService->getDailyReports();
        $this->assertEquals(1000, $dailyReports['totalWithdrawals']);
    }

    public function test_that_daily_reports_correct_total_fundings_amount()
    {
        WalletTransaction::query()->update([
            'transaction_type' => 'CREDIT',
            'description' => 'Fund Wallet',
            'created_at' => now(),
            'amount' => 100
        ]);

        $dailyReports = $this->reportsService->getDailyReports();
        $this->assertEquals(1000, $dailyReports['totalFundedAmount']);
    }

    public function test_that_daily_reports_correct_total_staked_amount()
    {
        Staking::query()->update([
            'created_at' => now(),
            'amount_staked' => 100
        ]);

        $dailyReports = $this->reportsService->getDailyReports();
        $this->assertEquals(1000, $dailyReports['totalStakedAmount']);
    }

    public function test_that_daily_reports_correct_total_amount_won()
    {
        Staking::query()->update([
            'created_at' => now(),
            'amount_won' => 100
        ]);

        $dailyReports = $this->reportsService->getDailyReports();
        $this->assertEquals(1000, $dailyReports['totalAmountWon']);
    }

    public function test_that_weekly_reports_returns_data()
    {
        $weeklyReports = $this->reportsService->getWeeklyReports();
        $this->assertCount(8, $weeklyReports);
    }

    public function test_that_weekly_reports_correct_total_amount_won()
    {
        Staking::query()->update([
            'created_at' => now(),
            'amount_won' => 200
        ]);

        $weeklyReports = $this->reportsService->getWeeklyReports();
        $this->assertEquals(2000, $weeklyReports['totalAmountWon']);
    }

    public function test_that_weekly_reports_correct_total_staked_amount()
    {
        Staking::query()->update([
            'created_at' => now(),
            'amount_staked' => 400
        ]);

        $weeklyReports = $this->reportsService->getWeeklyReports();
        $this->assertEquals(4000, $weeklyReports['totalStakedamount']);
    }

    public function test_that_weekly_reports_correct_total_withdrawal_amount()
    {
        WalletTransaction::query()->update([
            'transaction_type' => 'DEBIT',
            'description' => 'Winnings Withdrawal Made',
            'created_at' => now(),
            'amount' => 100
        ]);

        $weeklyReports = $this->reportsService->getWeeklyReports();
        $this->assertEquals(1000, $weeklyReports['totalWithdrawals']);
    }

    public function test_that_weekly_reports_correct_total_funding_amount()
    {
        WalletTransaction::query()->update([
            'transaction_type' => 'CREDIT',
            'description' => 'Fund Wallet',
            'created_at' => now(),
            'amount' => 100
        ]);

        $weeklyReports = $this->reportsService->getWeeklyReports();
        $this->assertEquals(1000, $weeklyReports['totalFundedAmount']);
    }

    public function test_that_weekly_reports_returns_stakers()
    {
        ExhibitionStaking::factory()
            ->count(20)
            ->create();

        GameSession::query()->update([
            'created_at' => now(),
        ]);

        $weeklyReports = $this->reportsService->getWeeklyReports();
        $this->assertCount(10, $weeklyReports['stakers']);
    }
}
