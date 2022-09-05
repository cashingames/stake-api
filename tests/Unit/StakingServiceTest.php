<?php

namespace Tests\Unit;

use App\Models\Staking;
use App\Models\Trivia;
use App\Models\User;;

use App\Services\StakingService;
use Database\Seeders\StakingSeeder;
use Database\Seeders\TriviaSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StakingServiceTest extends TestCase
{
    use RefreshDatabase;

    public $stakingService;
    public $user, $staking, $liveTrivia;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
        $this->seed(StakingSeeder::class);
        $this->seed(TriviaSeeder::class);
        $this->user = User::inRandomOrder()->first();
        $this->staking = Staking::inRandomOrder()->first();
        $this->liveTrivia = Trivia::inRandomOrder()->first();
        $this->stakingService = new StakingService($this->user);
    }

    public function test_that_an_amount_can_be_staked()
    {
        $this->user->wallet->update([
            'balance' => 5000
        ]);

        $stakingId = $this->stakingService->stakeAmount(1000);
        
        $this->assertIsInt($stakingId);
    }

    public function test_that_a_trivia_staking_record_can_be_created()
    {
        $this->stakingService->createTriviaStaking($this->staking->id, $this->liveTrivia->id);

        $this->assertDatabaseHas('trivia_stakings', [
            'staking_id' => $this->staking->id,
            'trivia_id' => $this->liveTrivia->id,
        ]);
    }
}
