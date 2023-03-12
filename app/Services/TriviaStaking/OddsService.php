<?php

namespace App\Services\TriviaStaking;

use App\Enums\FeatureFlags;
use App\Models\Staking;
use App\Models\StakingOdd;
use App\Models\StakingOddsRule;
use App\Models\User;
use App\Services\FeatureFlag;
use App\Traits\Utils\DateUtils;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class OddsService
{

    use DateUtils;

    public function getOdds($user)
    {
        // @TODO Rename to TRIVIA_STAKING_WITH_DYNAMIC_ODDS

        return FeatureFlag::isEnabled(FeatureFlags::STAKING_WITH_ODDS) ?
            $this->getDynamicOdds($user) :
            $this->getStandardOdds();
    }

    private function getStandardOdds(): Collection
    {
        return Cache::remember(
            'staking-odds',
            60 * 60,
            fn() => StakingOdd::active()->orderBy('score', 'DESC')->get()
        );
    }

    public function getDynamicOdds(User $user): Collection
    {
        $odds = $this->getStandardOdds();
        $oddsMultiplier = $this->computeDynamicOdds($user);

        //update odds multiplier variables inside Odds
        return $odds->map(function ($odd) use ($oddsMultiplier) {
            $odd->odd = round(($odd->odd * $oddsMultiplier['oddsMultiplier']), 2);
            return $odd;
        });
    }

    public function computeDynamicOdds(User $user): array
    {
        /**
         * @var \Illuminate\Support\Collection $stakingOddsRule
         */
        $stakingOddsRule = collect(Cache::remember('staking-odds-rule', 60 * 60, fn() => StakingOddsRule::get()));
        $placeHolder = new \stdClass();
        $placeHolder->odds_benefit = 1;
        $placeHolder->display_name = 'LESS_THAN_TARGET_PLATFORM_INCOME';

        //if platform is not making up to 30% profit
        // and if user is not new, return half odds (0.5)
        if ($this->getPlatformProfitToday() < config('trivia.platform_target')) {
            $rule = $stakingOddsRule->firstWhere('rule', 'LESS_THAN_TARGET_PLATFORM_INCOME') ?? $placeHolder;
            return [
                'oddsMultiplier' => $rule->odds_benefit,
                'oddsCondition' => $rule->display_name
            ];
        }

        return [
            'oddsMultiplier' => 1,
            'oddsCondition' => 'no_matching_condition'
        ];
    }

    /**
     * Platform profit is the opposite of total users profit
     * e,g if users profit is 10%, then platform profit is -10%
     *
     * @return float|int
     */
    private function getPlatformProfitToday(): float|int
    {
        $todayStakes = Cache::remember(
            "today_stakes",
            60,
            fn() => Staking::whereDate('created_at', '=', date('Y-m-d'))
                ->selectRaw('sum(amount_staked) as amount_staked, sum(amount_won) as amount_won')
                ->first()
        );
        $amountStaked = $todayStakes?->amount_staked ?? 0;
        $amountWon = $todayStakes?->amount_won ?? 0;


        /**
         * If no stakes were made today, then the platform is neutral
         * So first user should be lucky
         */
        if ($amountWon == 0) {
            return 100;
        }

        if ($amountStaked == 0) {
            return 0;
        }

        return (($amountWon - $amountStaked) / $amountStaked) * -100;
    }

}
