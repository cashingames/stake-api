<?php

namespace App\Services\TriviaStaking;

use App\Enums\FeatureFlags;
use App\Models\StakingOdd;
use App\Models\StakingOddsRule;
use App\Models\User;
use App\Repositories\Cashingames\WalletRepository;
use App\Services\FeatureFlag;
use App\Traits\Utils\DateUtils;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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
        $placeHolder = new \stdClass();
        $placeHolder->odds_benefit = 1;
        $placeHolder->display_name = 'LESS_THAN_TARGET_PLATFORM_INCOME';

        //if platform is not making up to 30% profit
        // and if user is not new, return half odds (0.5)
        $platformProfit = Cache::remember(
            'platform-profit-today',
            60*3,
            fn() => $this->walletRepository->getPlatformProfitPercentageOnStakingToday()
        );
        $platformTarget = config('trivia.platform_target');

        if ($platformProfit < $platformTarget) {
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

}
