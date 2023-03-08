<?php

namespace App\Services;

use App\Models\ExhibitionStaking;
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
    public $incompleteStakingSessionsCount;
    public $totalUsedBoostCount;
    public $totalPurchasedBoostCount;
    public $uniqueStakersCount;
    public $totalPurchasedBoostAmount;
    public $timeFreezeboostBoughtCount;
    public $timeFreezeboostBoughtAmount;
    public $skipBoostBoughtAmount;
    public $skipBoostBoughtCount;
    public $averageStakesPerStaker;
    public $averageBoostUsedPerGameSession;
    public $totalGameSessions;
    public $totalStakes;

    public function getDailyReports()
    {
        $startDate = $this->toNigeriaTimeZoneFromUtc(Carbon::yesterday()->startOfDay());
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

        $this->completedStakingSessionsCount = $this->getCompletedStakingSessionsCount($startDate, $endDate);

        $this->incompleteStakingSessionsCount = $this->getIcompleteStakingSessionsCount($startDate, $endDate);
        $this->totalUsedBoostCount = $this->getTotalUsedBoostCount($startDate, $endDate);
        $this->totalPurchasedBoostCount = $this->getTotalPurchasedBoostsCount($startDate, $endDate);
        $this->uniqueStakersCount = $this->getTotalNumberOfUniqueStakers($startDate, $endDate);

        $data = [
            'netProfit' => number_format($this->netProfit),
            'totalFundedAmount' => number_format($this->totalFundedAmount),
            'totalWithdrawals' => number_format($this->totalWithdrawals),
            'totalAmountWon' => number_format($this->totalAmountWon),
            'totalStakedAmount' => number_format($this->totalStakedamount),
            'completedStakingSessionsCount' => $this->completedStakingSessionsCount,
            'incompleteStakingSessionsCount' => $this->incompleteStakingSessionsCount,
            'totalUsedBoostCount' => $this->totalUsedBoostCount,
            'totalPurchasedBoostCount' =>  $this->totalPurchasedBoostCount,
            'uniqueStakersCount' => $this->uniqueStakersCount
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
        $this->stakers = $this->getTopStakers($startDate, $endDate)->get();
        $this->totalFundedAmount = WalletTransaction::where('transaction_type', 'CREDIT')
            ->where('description', 'Fund Wallet')->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->sum('amount');
        $this->totalWithdrawals = WalletTransaction::where('transaction_type', 'DEBIT')
            ->where('description', 'Winnings Withdrawal Made')->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)->sum('amount');
        $this->completedStakingSessionsCount = $this->getCompletedStakingSessionsCount($startDate, $endDate);
        $this->incompleteStakingSessionsCount = $this->getIcompleteStakingSessionsCount($startDate, $endDate);
        $this->totalUsedBoostCount = $this->getTotalUsedBoostCount($startDate, $endDate);
        $this->totalPurchasedBoostCount = $this->getTotalPurchasedBoostsCount($startDate, $endDate);
        $this->uniqueStakersCount = $this->getTotalNumberOfUniqueStakers($startDate, $endDate);
        $this->timeFreezeboostBoughtAmount = $this->getTimeFreezePurchasedBoostsAmount($startDate, $endDate);
        $this->timeFreezeboostBoughtCount = $this->getTimeFreezePurchasedBoostsCount($startDate, $endDate);
        $this->skipBoostBoughtAmount = $this->getSkipPurchasedBoostsAmount($startDate, $endDate);
        $this->skipBoostBoughtCount = $this->getSkipPurchasedBoostsCount($startDate, $endDate);
        $this->totalPurchasedBoostAmount = $this->getTotalPurchasedBoostAmount($startDate, $endDate);
        $this->totalGameSessions = $this->getTotalGameSessions($startDate, $endDate);
        $this->totalStakes = $this->getTotalStakes($startDate, $endDate) ;
        // dd($this->totalGameSessions );
        $this->averageBoostUsedPerGameSession = $this->totalUsedBoostCount/$this->totalGameSessions;
        $this->averageStakesPerStaker = $this->uniqueStakersCount/$this->totalStakes ;

        $data = [
            'netProfit' => number_format($this->netProfit),
            'stakers' => $this->stakers,
            'totalFundedAmount' => number_format($this->totalFundedAmount),
            'totalWithdrawals' => number_format($this->totalWithdrawals),
            'completedStakingSessionsCount' => $this->completedStakingSessionsCount,
            'totalAmountWon' => number_format($this->totalAmountWon),
            'totalStakedamount' => number_format($this->totalStakedamount),
            'incompleteStakingSessionsCount' => $this->incompleteStakingSessionsCount,
            'totalUsedBoostCount' => $this->totalUsedBoostCount,
            'totalPurchasedBoostCount' =>  $this->totalPurchasedBoostCount,
            'uniqueStakersCount' => $this->uniqueStakersCount,
            'totalPurchasedBoostAmount' => number_format($this->totalPurchasedBoostAmount),
            'timeFreezeboostBoughtAmount' => number_format($this->timeFreezeboostBoughtAmount),
            'timeFreezeboostBoughtCount' => $this->timeFreezeboostBoughtCount,
            'skipBoostBoughtAmount' => number_format($this->skipBoostBoughtAmount),
            'skipBoostBoughtCount' => $this->skipBoostBoughtCount,
            'averageStakesPerStaker' => round($this->averageStakesPerStaker, 3),
            'averageBoostUsedPerGameSession' => round($this->averageBoostUsedPerGameSession, 3),
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

    private function getTopStakers($startDate, $endDate)
    {

        $stakers =  DB::table('game_sessions')
            ->select(
                "users.username",
                DB::raw('SUM(stakings.amount_won) AS amount_won'),
                DB::raw('SUM(stakings.amount_staked) AS amount_staked'),
                DB::raw('COUNT(game_sessions.id) AS game_session_count')
            )
            ->where('game_sessions.created_at', '>=', $startDate)
            ->where('game_sessions.created_at', '<=', $endDate)
            ->join("exhibition_stakings", "exhibition_stakings.game_session_id", "=", "game_sessions.id")
            ->join("stakings", "stakings.id", "=", "exhibition_stakings.staking_id")
            ->join("users", "users.id", "=", "game_sessions.user_id")
            ->groupBy('game_sessions.user_id')->orderBy('amount_won','DESC')->limit(10);
        return $stakers;
    }

    private function getCompletedStakingSessionsCount($startDate, $endDate)
    {
        return GameSession::whereHas('exhibitionStaking')->where('state', 'COMPLETED')
            ->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)->count();
    }

    private function getIcompleteStakingSessionsCount($startDate, $endDate)
    {
        return GameSession::whereHas('exhibitionStaking')->where('state', 'ONGOING')
            ->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)->count();
    }

    private function getTotalUsedBoostCount($startDate, $endDate)
    {
        return DB::table('exhibition_boosts')->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)->count();
    }
    private function getTotalPurchasedBoostsCount($startDate, $endDate){
       return WalletTransaction::where('description','LIKE','Bought TIME FEEZE boosts')
        ->orWhere('description','LIKE','Bought SKIP boosts')->where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $endDate)->count();
    }

    private function getTotalNumberOfUniqueStakers($startDate, $endDate){
        return Staking::where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $endDate)->groupBy('user_id')->count();
    }
    private function getTotalPurchasedBoostAmount($startDate, $endDate){
        return WalletTransaction::where('description','LIKE','Bought TIME FEEZE boosts')
        ->orWhere('description','LIKE','bought SKIP boosts')->where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $endDate)->sum('amount');
    }

    private function getTimeFreezePurchasedBoostsCount($startDate, $endDate){
        return WalletTransaction::where('description','LIKE','Bought TIME FEEZE boosts')->where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $endDate)->count();
    }

    private function getTimeFreezePurchasedBoostsAmount($startDate, $endDate){
        return WalletTransaction::where('description','LIKE','Bought TIME FEEZE boosts')->where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $endDate)->sum('amount');
    }
    private function getSkipPurchasedBoostsAmount($startDate, $endDate){
        return WalletTransaction::where('description','LIKE','Bought SKIP boosts')->where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $endDate)->sum('amount');
    }
    private function getSkipPurchasedBoostsCount($startDate, $endDate){
        return WalletTransaction::where('description','LIKE','Bought SKIP boosts')->where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $endDate)->count();
    }

    private function getTotalStakes($startDate, $endDate){
        return Staking::where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $endDate)->count();
    }

    private function getTotalGameSessions($startDate, $endDate){
        return GameSession::where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)->count();
    }
}
