<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\GameSession;
use App\Models\Staking;
use App\Models\WalletTransaction;
use App\Traits\Utils\DateUtils;
use Illuminate\Support\Facades\DB;

class AutomatedReportsService
{
    use DateUtils;

    public $netProfit;
    public $totalStakedamount;
    public $totalAmountWon;
    public $totalFundedAmount;
    public $stakers;
    public $completedStakingSessionCount;
    public $totalWithdrawals;
    public $completedStakingSessionsCount;

    public function getDailyReports()
    {
        $startDate = $this->toNigeriaTimeZoneFromUtc(Carbon::yesterday()->endOfDay());
        $endDate = $this->toNigeriaTimeZoneFromUtc(Carbon::yesterday()->endOfDay());


        $this->netProfit = $this->getPlatformProfit($startDate, $endDate);

        $this->totalFundedAmount = WalletTransaction::where('transaction_type', 'CREDIT')
            ->where('description', 'Fund Wallet')->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->sum('amount');
        $this->totalWithdrawals = WalletTransaction::where('transaction_type', 'DEBIT')
            ->where('description', 'Winnings Withdrawal Made')->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)->sum('amount');

        $this->totalAmountWon = Staking::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->sum('amount_won');

        $this->totalStakedamount = Staking::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->sum('amount_staked');

        $data = [
            'netProfit' => number_format($this->netProfit),
            'totalFundedAmount' => number_format($this->totalFundedAmount),
            'totalWithdrawals' => number_format($this->totalWithdrawals),
            'totalAmountWon' => number_format($this->totalAmountWon),
            'totalStakedAmount' => number_format($this->totalStakedamount)
        ];

        return $data;
    }

    public function getWeeklyReports()
    {
        $startDate = $this->toNigeriaTimeZoneFromUtc(Carbon::yesterday()->startOfWeek());
        $endDate = $this->toNigeriaTimeZoneFromUtc(Carbon::yesterday()->endOfWeek());

        $this->totalAmountWon = Staking::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->sum('amount_won');
        $this->totalStakedamount = Staking::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->sum('amount_staked');
        $this->netProfit = $this->getPlatformProfit($startDate, $endDate);
        $this->stakers = $this->getStakers($startDate, $endDate)->get();
        $this->totalFundedAmount = WalletTransaction::where('transaction_type', 'CREDIT')
            ->where('description', 'Fund Wallet')->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->sum('amount');
        $this->totalWithdrawals = WalletTransaction::where('transaction_type', 'DEBIT')
            ->where('description', 'Winnings Withdrawal Made')->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)->sum('amount');
        $this->completedStakingSessionsCount = $this->getCompletedStakingSessionsCount($startDate, $endDate);

        $data = [
            'netProfit' => number_format($this->netProfit),
            'stakers' => $this->stakers,
            'totalFundedAmount' => number_format($this->totalFundedAmount),
            'totalWithdrawals' => number_format($this->totalWithdrawals),
            'completedStakingSessionsCount' => $this->completedStakingSessionsCount,
            'numberOfStakers' => $this->stakers->count(),
            'totalAmountWon' => number_format($this->totalAmountWon),
            'totalStakedamount' => number_format($this->totalStakedamount)
        ];

        return $data;
    }

    private function getPlatformProfit($startDate, $endDate)
    {
        $stakes = Staking::where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate);

        $amountStaked = $stakes->sum('stakings.amount_staked') ?? 0;
        $amountWon = $stakes->sum('stakings.amount_won') ?? 0;

        if ($amountStaked == 0) {
            return 0;
        }

        return $amountStaked - $amountWon;
    }

    private function getStakers($startDate, $endDate)
    {

        $stakers =  DB::table('game_sessions')
            ->select(
                "stakings.amount_won",
                "stakings.amount_staked",
                "users.username",
                DB::raw('SUM(stakings.amount_won) AS amount_won'),
                DB::raw('SUM(stakings.amount_staked) AS amount_staked')
            )
            ->where('game_sessions.created_at', '>=', $startDate)
            ->where('game_sessions.created_at', '<=', $endDate)
            ->join("exhibition_stakings", "exhibition_stakings.game_session_id", "=", "game_sessions.id")
            ->join("stakings", "stakings.id", "=", "exhibition_stakings.staking_id")
            ->join("users", "users.id", "=", "game_sessions.user_id")
            ->groupBy('game_sessions.user_id')->orderBy('stakings.amount_won')->limit(10);
        return $stakers;
    }

    private function getCompletedStakingSessionsCount($startDate, $endDate)
    {
        return GameSession::whereHas('exhibitionStaking')->where('state', 'COMPLETED')
            ->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)->count();
    }
}
