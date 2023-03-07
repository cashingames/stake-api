<?php

namespace App\Services;

use App\Models\StakingOddsRule;
use App\Models\User;
use App\Traits\Utils\DateUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class StakingOddsComputer
{

    use DateUtils;

    public function compute(User $user): array
    {
        $percentWonToday = $this->getPercentageWonToday($user);

        $stakingOddsRule = Cache::remember('staking-odds', 60*60, fn () => StakingOddsRule::all());

        $oddsMultiplier = 1;
        $oddsCondition = "no_matching_condition";

        if ($this->isNewPlayer($user)) {
            $newUserRulesAndConditions = $stakingOddsRule->where('rule', 'GAME_COUNT_LESS_THAN_5')->first();
            $oddsMultiplier = $newUserRulesAndConditions->odds_benefit;
            $oddsCondition = $newUserRulesAndConditions->display_name;
        } elseif ($percentWonToday <= 0.5) {
            $lowScoreRulesAndConditions = $stakingOddsRule->where('rule', 'AVERAGE_SCORE_LESS_THAN_5')->first();
            $oddsMultiplier = $lowScoreRulesAndConditions->odds_benefit;
            $oddsCondition = $lowScoreRulesAndConditions->display_name;
        } elseif ($percentWonToday <= 1) {
            $moderateScoreRulesAndConditions =
                $stakingOddsRule->where('rule', 'AVERAGE_SCORE_BETWEEN_5_AND_7')->first();
            $oddsMultiplier = $moderateScoreRulesAndConditions->odds_benefit;
            $oddsCondition = $moderateScoreRulesAndConditions->display_name;
        } elseif ($percentWonToday > 1) {
            $highScoreRulesAndConditions = $stakingOddsRule->where('rule', 'AVERAGE_SCORE_GREATER_THAN_7')->first();
            $oddsMultiplier = $highScoreRulesAndConditions->odds_benefit;
            $oddsCondition = $highScoreRulesAndConditions->display_name;
        }

        if ($this->isFirstGameAfterFunding($user)) {
            $fundingWalletRulesAndConditions =
                $stakingOddsRule->where('rule', 'FIRST_TIME_GAME_AFTER_FUNDING')->first();
            $oddsMultiplier += $fundingWalletRulesAndConditions->odds_benefit;
            $oddsCondition .= "_and_" . $fundingWalletRulesAndConditions->display_name;
        }
        return [
            'oddsMultiplier' => $oddsMultiplier,
            'oddsCondition' => $oddsCondition
        ];
    }

    public function isFirstGameAfterFunding(User $user)
    {
        /**
         * @TODO Convert this one to one query most likely in a query scope
         */
        $lastFund = $user->transactions()
            ->where('transaction_type', 'CREDIT')
            ->where('description', 'like', "fund%")
            ->latest()->first();
        $lastGame = $user->gameSessions()->latest()->first();
        if (is_null($lastFund) || is_null($lastGame)) {
            return false;
        }

        return Carbon::createFromDate($lastFund->created_at)->gt(Carbon::createFromDate($lastGame->created_at));
    }

    public function isNewPlayer(User $user)
    {
        return $user->gameSessions()->count() <= 3;
    }


    private function getPercentageWonToday($user): float
    {
        $todayStakes = $user->gameSessions()
            ->join('exhibition_stakings', 'game_sessions.id', '=', 'exhibition_stakings.game_session_id')
            ->join('stakings', 'exhibition_stakings.staking_id', '=', 'stakings.id')
            ->whereDate('game_sessions.created_at', '=', date('Y-m-d'));

        $amountStaked = $todayStakes->sum('stakings.amount_staked') ?? 1;
        $amountWon = $todayStakes->sum('stakings.amount_won') ?? 1;

        if ($amountStaked == 0 || $amountWon == 0) {
            return 1;
        }

        return $amountWon / $amountStaked;
    }

}
