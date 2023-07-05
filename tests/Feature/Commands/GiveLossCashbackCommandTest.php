<?php
namespace Tests\Feature;

use App\Models\Staking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiveLossCashbackCommandTest extends TestCase
{

    use RefreshDatabase;

    public function test_cashback_given_to_user_after_losses_daily()
    {
        User::factory()
            ->count(5)
            ->hasProfile(1)
            ->hasWallet(1)
            ->create();

        $user = User::first();
        $user2 = User::skip(1)->first();
        $user3 = User::skip(2)->first();

        Staking::factory()->for($user)->create([
            'amount_staked' => 1000,
            'amount_won' => 500,
        ]);
        Staking::factory()->for($user2)->create([
            'amount_staked' => 600,
            'amount_won' => 500,
        ]);
        Staking::factory()->for($user3)->create([
            'amount_staked' => 100,
            'amount_won' => 1000,
        ]);

        Staking::factory()->for($user2)->create([
            'amount_staked' => 600,
            'amount_won' => 500,
        ]);


        $this->artisan('bonus:stake:loss-cashback --duration=daily')->assertExitCode(0);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'bonus' => 500 * 0.1,
        ]);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $user3->id,
            'bonus' => 0,
        ]);
    }

    public function test_cashback_given_to_user_after_losses_weekly()
    {
        User::factory()
            ->count(5)
            ->hasProfile(1)
            ->hasWallet(1)
            ->create();

        $user = User::first();
        $user2 = User::skip(1)->first();
        $user3 = User::skip(2)->first();

        Staking::factory()->for($user)->create([
            'amount_staked' => 1000,
            'amount_won' => 500,
        ]);
        Staking::factory()->for($user2)->create([
            'amount_staked' => 600,
            'amount_won' => 500,
        ]);
        Staking::factory()->for($user3)->create([
            'amount_staked' => 100,
            'amount_won' => 1000,
        ]);

        Staking::factory()->for($user2)->create([
            'amount_staked' => 600,
            'amount_won' => 500,
        ]);


        $this->artisan('bonus:stake:loss-cashback --duration=weekly')->assertExitCode(0);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'bonus' => 500 * 0.1,
        ]);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $user3->id,
            'bonus' => 0,
        ]);
    }
}