<?php

namespace App\Services;

use App\Models\StakingOddsRule;
use App\Models\User;
use App\Traits\Utils\DateUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StakingOddsComputer
{

    use DateUtils;

    public function compute(User $user): array
    {
        $todaysData = $this->getTodaysData($user);

        $percentWonToday = $todaysData['percentWonToday'];
        $gameCount = $todaysData['gameCount'];

        $stakingOddsRule = Cache::remember('staking-odds-rule', 60 * 60, fn() => StakingOddsRule::get());

        $oddsMultiplier = 1;
        $oddsCondition = "no_matching_condition";

        if ($gameCount <= 3) {
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

        return [
            'oddsMultiplier' => $oddsMultiplier,
            'oddsCondition' => $oddsCondition
        ];
    }

    private function getTodaysData($user): array
    {
        $todayStakes = $user->gameSessions()
            ->join('exhibition_stakings', 'game_sessions.id', '=', 'exhibition_stakings.game_session_id')
            ->join('stakings', 'exhibition_stakings.staking_id', '=', 'stakings.id')
            ->select(
                DB::raw(
                    'sum(stakings.amount_staked) as amount_staked,
                sum(stakings.amount_won) as amount_won,
                count(stakings.id) as count'
                )
            )
            ->whereDate('game_sessions.created_at', '=', date('Y-m-d'))
            ->first();


        $amountStaked = $todayStakes?->amount_staked ?? 1;
        $amountWon = $todayStakes?->amount_amount_won ?? 1;

        if ($amountStaked == 0 || $amountWon == 0) {
            return ['percentWonToday' => 1, 'gameCount' => 0];
        }

        return ['percentWonToday' => $amountWon / $amountStaked, 'gameCount' => $todayStakes?->count ?? 0];
    }

}
