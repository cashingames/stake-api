<?php

namespace Tests\Feature;

use BoostSeeder;
use UserSeeder;
use Tests\TestCase;
use App\Models\Boost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    const GAME_COMMON_DATA_URL = '/api/v3/game/common';
    const BUY_BOOST_WALLET_URL = '/api/v3/wallet/buy-boosts';
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
        $this->seed(BoostSeeder::class);
        $this->user = User::first();

        $this->actingAs($this->user);
    }

    //Boost Test Cases
    public function test_boosts_can_be_fetched()
    {
        $this->seed(BoostSeeder::class);
        $response = $this->get(self::GAME_COMMON_DATA_URL);

        $response->assertJsonStructure([
            'data' => [
                'boosts',
            ]
        ]);
        $response->assertStatus(200);
    }

    public function test_a_boost_must_first_exist_to_be_bought()
    {
        $response = $this->post(self::BUY_BOOST_WALLET_URL . '/50');
        $response->assertJsonFragment(['message' => 'Wrong boost selected']);

        $response->assertStatus(400);
    }

    public function test_boosts_can_be_bought_from_wallet()
    {
        $this->user->wallet->update(['non_withdrawable' => 1000]);

        $response = $this->post(self::BUY_BOOST_WALLET_URL . '/' . Boost::inRandomOrder()->first()->id);
        $response->assertJsonFragment(['message' => 'Boost Bought']);

        $response->assertStatus(200);
    }


    public function test_boost_cannot_be_bought_if_wallet_balance_is_less_than_boost_currency_value()
    {
        $response = $this->post(self::BUY_BOOST_WALLET_URL . '/' . Boost::inRandomOrder()->first()->id);
        $response->assertJsonFragment(['message' => 'You do not have enough money in your wallet.']);

        $response->assertStatus(400);
    }

}
