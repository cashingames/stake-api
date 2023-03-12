<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\StakingOdd;
use App\Enums\FeatureFlags;
use App\Services\FeatureFlag;
use App\Models\StakingOddsRule;
use Illuminate\Support\Facades\Cache;
use App\Services\TriviaStaking\OddsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OddsServiceTest extends TestCase
{
    use RefreshDatabase;

    private OddsService $oddsService;

    public function setUp(): void
    {
        parent::setUp();

        $this->oddsService = new OddsService();
    }

    public function test_first_time_user_should_get_static_odds()
    {
        Cache::shouldReceive('remember')->andReturn(
            collect(
                [
                    StakingOdd::make([
                        'id' => 1,
                        'score' => 10,
                        'odd' => 10
                    ])
                ]
            )
        );

        $odds = $this->oddsService->getOdds(User::make());
        $this->assertEquals(
            $odds,
            collect(
                [
                    StakingOdd::make([
                        'id' => 1,
                        'score' => 10,
                        'odd' => 10
                    ])
                ]
            )
        );
    }

    public function test_that_all_users_get_variable_lower_odd_if_plaform_target_not_met()
    {

        config(['trivia.platform_target' => 50]);
        FeatureFlag::enable(FeatureFlags::STAKING_WITH_ODDS);

        Cache::shouldReceive('remember')->withSomeOfArgs(
            "staking-odds",
        )->andReturn(
                collect(
                    [
                        StakingOdd::make([
                            'id' => 1,
                            'score' => 10,
                            'odd' => 10
                        ])
                    ]
                )
            );
        Cache::shouldReceive('remember')->withSomeOfArgs(
            "staking-odds-rule"
        )->andReturn(
                collect(
                    [
                        StakingOddsRule::make([
                            'rule' => 'LESS_THAN_TARGET_PLATFORM_INCOME',
                            'display_name' => 'LESS_THAN_TARGET_PLATFORM_INCOME',
                            'odds_benefit' => 0.5
                        ])
                    ]
                )
            );

        Cache::shouldReceive('remember')->withSomeOfArgs(
            "today_stakes",
        )->andReturn(
                (object) ["amount_staked" => 100, "amount_won" => 90]
            );

        $odds = $this->oddsService->getOdds(User::make());
        $this->assertEquals(
            $odds,
            collect(
                [
                    StakingOdd::make([
                        'id' => 1,
                        'score' => 10,
                        'odd' => 5 //got halved because of the odds_benefit
                    ])
                ]
            )
        );
    }

}
