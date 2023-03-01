<?php

namespace App\Services;

use App\Models\Category;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Contest;
use App\Models\GameSession;
use App\Models\Staking;
use App\Models\WalletTransaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\StakingService;
use App\Traits\Utils\DateUtils;

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
        $startDate = $this->toNigeriaTimeZoneFromUtc(Carbon::today()->startOfDay());
        $endDate = $this->toNigeriaTimeZoneFromUtc(Carbon::today()->endOfDay());


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
            'netProfitAndLoss' => $this->netProfit,
            'totalFundedAmount' => $this->totalFundedAmount,
            'totalWithdrawals' => $this->totalWithdrawals,
            'totalAmountWon' => $this->totalAmountWon,
            'totalStakedAmount' => $this->totalStakedamount
        ];

        return $data;
    }

    public function getWeeklyReports()
    {
        $startDate = $this->toNigeriaTimeZoneFromUtc(Carbon::now()->startOfWeek());
        $endDate = $this->toNigeriaTimeZoneFromUtc(Carbon::now()->endOfWeek());

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
            'netProfitAndLoss' => $this->netProfit,
            'stakers' => $this->stakers,
            'totalFundedAmount' => $this->totalFundedAmount,
            'totalWithdrawals' => $this->totalWithdrawals,
            'completedStakingSessionsCount' => $this->completedStakingSessionsCount,
            'numberOfStakers' => $this->stakers->count(),
            'totalAmountWon' => $this->totalAmountWon,
            'totalStakedamount' => $this->totalStakedamount
        ];

        return $data;
    }

    private function getPlatformProfit($startDate, $endDate)
    {
        $stakes = Staking::where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate);

        $amountStaked = $stakes->sum('stakings.amount_staked') ?? 0;
        $amountWon = $stakes->sum('stakings.amount_won') ?? 0;

        if ($amountStaked == 0) {
            return 1;
        }

        return 1 - ($amountWon / $amountStaked);
    }

    private function getStakers($startDate, $endDate)
    {
        $stakers =  GameSession::select(
            "users.email",
            "stakings.amount_won",
            "stakings.amount_staked",
            "users.username",
            "users.phone_number",
            "game_sessions.created_at"
        )
            ->where('game_sessions.created_at', '>=', $startDate)
            ->where('game_sessions.created_at', '<=', $endDate)
            ->join("exhibition_stakings", "exhibition_stakings.game_session_id", "=", "game_sessions.id")
            ->join("stakings", "stakings.id", "=", "exhibition_stakings.staking_id")
            ->join("users", "users.id", "=", "game_sessions.user_id")->orderBy('stakings.amount_won');
        return $stakers;
    }

    private function getCompletedStakingSessionsCount($startDate, $endDate)
    {
        return GameSession::whereHas('exhibitionStaking')->where('state', 'COMPLETED')
            ->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)->count();
    }
}
