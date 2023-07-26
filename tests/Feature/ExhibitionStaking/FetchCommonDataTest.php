<?php

namespace Tests\Feature;

use App\Models\Category;
use UserSeeder;
use BoostSeeder;
use CategorySeeder;
use GameModeSeeder;
use GameTypeSeeder;
use Tests\TestCase;
use App\Models\User;
use App\Models\GameSession;
use Database\Seeders\BonusSeeder;
use Database\Seeders\StakingOddSeeder;
use Database\Seeders\StakingOddsRulesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FetchCommonDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    const COMMON_DATA_URL = '/api/v3/game/common';
    protected $user;
    protected $category;
    protected $staking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->seed(BoostSeeder::class);
        $this->seed(GameTypeSeeder::class);
        $this->seed(GameModeSeeder::class);
        $this->seed(StakingOddSeeder::class);
        $this->seed(StakingOddsRulesSeeder::class);
        $this->seed(BonusSeeder::class);

        GameSession::factory()
            ->count(20)
            ->create();
        $this->user = User::first();
        $this->category = Category::where('category_id', '!=', 0)->inRandomOrder()->first();
        $this->actingAs($this->user);
        config(['odds.maximum_exhibition_staking_amount' => 1000]);
        config(['trivia.bonus.signup.stakers_bonus_amount' => 1000]);
    }

    public function test_common_data_can_be_retrieved()
    {
        $response = $this->get(self::COMMON_DATA_URL);

        $response->assertStatus(200);
    }

    public function test_common_data_can_be_retrieved_with_data()
    {
       
        $response = $this->get(self::COMMON_DATA_URL);

        $response->assertJsonStructure([
            'data' => [
                'boosts' => [],
                'gameModes' => [],
                'gameTypes' => [],
                'minVersionCode' => [],
                'minimumExhibitionStakeAmount' => [],
                'maximumExhibitionStakeAmount' =>[],
                'minimumChallengeStakeAmount' => [],
                'maximumChallengeStakeAmount' => [],
                'minimumWithdrawableAmount' => [],
                'maximumWithdrawableAmount' => [],
                'minimumWalletFundableAmount' => [],
                
            ]
        ]);
    }

 

 


}
