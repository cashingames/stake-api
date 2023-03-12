<?php

namespace Tests\Feature;

use App\Enums\FeatureFlags;
use App\Models\StakingOdd;
use App\Models\StakingOddsRule;
use App\Models\User;
use App\Services\FeatureFlag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetStakingOddsTest extends TestCase
{
    use RefreshDatabase;


    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());

    }
    public function test_that_odds_are_halved_when_platform_target_is_not_met_when_staking_odds_is_on()
    {
        FeatureFlag::enable(FeatureFlags::STAKING_WITH_ODDS);

        $expectedData = [
            [
                "id" => 1,
                "score" => 10,
                "odd" => 5,
            ]
        ];

        StakingOddsRule::factory()->create([
            'rule' => 'LESS_THAN_TARGET_PLATFORM_INCOME',
            'odds_benefit' => 0.5,
            'display_name' => 'less_than_target_platform_income',
            'odds_operation' => 0.5
        ]);

        StakingOdd::factory()->create([
            'score' => 10,
            'odd' => 10
        ]);


        $response = $this->get('/api/v3/odds/standard');

        $response->assertJsonPath('data', $expectedData);

    }

    public function test_that_odds_is_not_modified_when_staking_odd_is_off()
    {
        FeatureFlag::disable(FeatureFlags::STAKING_WITH_ODDS);

        $expectedData = [
            [
                "id" => 1,
                "score" => 10,
                "odd" => 10,
            ]
        ];

        StakingOddsRule::factory()->create([
            'rule' => 'LESS_THAN_TARGET_PLATFORM_INCOME',
            'odds_benefit' => 0.5,
            'display_name' => 'less_than_target_platform_income',
            'odds_operation' => 0.5
        ]);

        StakingOdd::factory()->create([
            'score' => 10,
            'odd' => 10
        ]);


        $response = $this->get('/api/v3/odds/standard');

        $response->assertJsonPath('data', $expectedData);

    }

}
