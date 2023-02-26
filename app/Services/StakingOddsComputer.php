<?php

namespace App\Services;

use App\Models\StakingOddsRule;
use App\Models\User;
use App\Traits\Utils\DateUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StakingOddsComputer
{

    use DateUtils;

    public function compute(User $user): array
    {
        $percentWonToday = $this->getPercentageWonToday($user);

        $oddsMultiplier = 1;
        $oddsCondition = "no_matching_condition";

        if ($this->isNewPlayer($user)) {
            $newUserRulesAndConditions = StakingOddsRule::where('rule', 'GAME_COUNT_LESS_THAN_5')->first();
            $oddsMultiplier = $newUserRulesAndConditions->odds_benefit;
            $oddsCondition = $newUserRulesAndConditions->display_name;
        } elseif ($percentWonToday <= 0.5) {
            $lowScoreRulesAndConditions = StakingOddsRule::where('rule', 'AVERAGE_SCORE_LESS_THAN_5')->first();
            $oddsMultiplier = $lowScoreRulesAndConditions->odds_benefit;
            $oddsCondition = $lowScoreRulesAndConditions->display_name;
        } elseif ($percentWonToday <= 1) {
            $moderateScoreRulesAndConditions = StakingOddsRule::where('rule', 'AVERAGE_SCORE_BETWEEN_5_AND_7')->first();
            $oddsMultiplier = $moderateScoreRulesAndConditions->odds_benefit;
            $oddsCondition = $moderateScoreRulesAndConditions->display_name;
        } elseif ($percentWonToday > 1) {
            $highScoreRulesAndConditions = StakingOddsRule::where('rule', 'AVERAGE_SCORE_GREATER_THAN_7')->first();
            $oddsMultiplier = $highScoreRulesAndConditions->odds_benefit;
            $oddsCondition = $highScoreRulesAndConditions->display_name;
        }

        if ($this->currentTimeIsInSpecialHours() && $percentWonToday <= 0.5) {

            $specialHourRulesAndConditions = StakingOddsRule::where('rule', 'AT_SPECIAL_HOUR')->first();
            $oddsMultiplier += $specialHourRulesAndConditions->odds_benefit;
            $oddsCondition .= "_and_" . $specialHourRulesAndConditions->display_name;
        }
        if ($this->isFirstGameAfterFunding($user)) {

            $fundingWalletRulesAndConditions = StakingOddsRule::where('rule', 'FIRST_TIME_GAME_AFTER_FUNDING')->first();
            $oddsMultiplier += $fundingWalletRulesAndConditions->odds_benefit;
            $oddsCondition .= "_and_" . $fundingWalletRulesAndConditions->display_name;
        }
        return [
            'oddsMultiplier' => $oddsMultiplier,
            'oddsCondition' => $oddsCondition
        ];
    }

    public function currentTimeIsInSpecialHours()
    {
        $now = date("H");
        $now = $this->toNigeriaTimeZoneFromUtc(date("Y-m-d H:i:s"))->format("H");
        $now .= ":00";

        $specialHours = config('odds.special_hours');

        return in_array($now, $specialHours);
    }

    public function isFirstGameAfterFunding(User $user)
    {
        $last_funding = $user->transactions()->where('transaction_type', 'CREDIT')->where('description', 'like', "fund%")->latest()->first();
        $last_game = $user->gameSessions()->latest()->first();
        if (is_null($last_funding) || is_null($last_game)) {
            return false;
        }

        return Carbon::createFromDate($last_funding->created_at)->gt(Carbon::createFromDate($last_game->created_at));
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