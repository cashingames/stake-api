<?php

namespace App\Services\TriviaStaking;

use App\Models\User;
use App\Models\StakingOdd;
use App\Enums\FeatureFlags;
use App\Services\FeatureFlag;
use App\Models\StakingOddsRule;
use App\Traits\Utils\DateUtils;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Cashingames\WalletRepository;

class OddsService
{

    use DateUtils;

    public function __construct(
        private WalletRepository $walletRepository
    )
    {
    }

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

        //if platform is not making up to 30% profit
        // and if user is not new, return half odds (0.5)
        $platformProfit = Cache::remember(
            'platform-profit-today',
            60*3,
            fn() => $this->walletRepository->getPlatformProfitPercentageOnStakingToday()
        );

        $platformTarget = config('trivia.platform_target');
        if ($platformProfit < $platformTarget) {
            $placeHolder = new \stdClass();
            $placeHolder->odds_benefit = 0.5;
            $placeHolder->display_name = 'LESS_THAN_TARGET_PLATFORM_INCOME';
            $rule = $stakingOddsRule->firstWhere('rule', 'LESS_THAN_TARGET_PLATFORM_INCOME') ?? $placeHolder;
            return [
                'oddsMultiplier' => $rule->odds_benefit,
                'oddsCondition' => $rule->display_name
            ];
        }

        //if user is new, return double odds (2)


        return [
            'oddsMultiplier' => 1,
            'oddsCondition' => 'no_matching_condition'
        ];
    }

}
