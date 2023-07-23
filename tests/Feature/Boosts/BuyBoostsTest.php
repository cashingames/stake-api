<?php

namespace Tests\Feature\Boosts;

use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Enums\WalletTransactionType;
use App\Models\Boost;
use App\Models\User;
use Database\Seeders\BoostSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyBoostsTest extends TestCase
{

    use RefreshDatabase;

    private const BUY_BOOST_URL = '/api/v3/boosts/1/buy';
    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        User::factory()
            ->count(5)
            ->hasProfile(1)
            ->hasWallet(1)
            ->create();
        $this->seed(BoostSeeder::class);

        $this->user = User::first();
        $this->actingAs($this->user);
    }

    /**
     * @dataProvider walletTypeDataProvider
     */
    public function test_that_a_user_can_buy_boosts($walletType)
    {
        $boost = Boost::first();
        $this->user->wallet->update([
            'non_withdrawable' => 1000,
            'withdrawable' => 1000,
            'bonus' => 300,
        ]);
        $response = $this->post(self::BUY_BOOST_URL, [
            'id' => $boost->id,
            'wallet_type' => $walletType
        ]);

        $this->assertDatabaseHas('user_boosts', [
            'user_id' => $this->user->id,
            'boost_id' => $boost->id,
            'boost_count' => $boost->pack_count,
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $this->user->wallet->id,
            'amount' => $boost->price,
            'description' => "Bought boost {$boost->name}",
            'balance_type' => $walletType,
            'transaction_type' => WalletTransactionType::Debit->value,
            'transaction_action' => WalletTransactionAction::BoostBought->value,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Boost purchased successfully']);
    }

    public function test_that_a_user_with_boosts_can_buy_more()
    {
        $boost = Boost::first();
        $boost->users()->attach($this->user->id, [
            'boost_count' => 1,
            'used_count' => 0
        ]);
        $this->user->wallet->update([
            'non_withdrawable' => 1000,
            'withdrawable' => 1000,
            'bonus' => 300,
        ]);
        $response = $this->post(self::BUY_BOOST_URL, [
            'id' => $boost->id,
            'wallet_type' => WalletBalanceType::CreditsBalance->value,
        ]);

        $this->assertDatabaseHas('user_boosts', [
            'user_id' => $this->user->id,
            'boost_id' => $boost->id,
            'boost_count' => $boost->pack_count + 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Boost purchased successfully']);
    }

    /**
     * @dataProvider walletTypeDataProvider
     */
    public function test_that_a_user_cannot_buy_boost_with_insufficient_wallet_balance($walletType)
    {
        $this->user->wallet->update([
            'non_withdrawable' => 30,
            'withdrawable' => 30,
            'bonus' => 30,
        ]);
        $response = $this->postJson(self::BUY_BOOST_URL, [
            'id' => 1,
            'wallet_type' => $walletType,
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Insufficient wallet balance to proceed']);
    }

    public function test_that_a_user_cannot_buy_boost_with_wrong_wallet_type()
    {
        $response = $this->postJson(self::BUY_BOOST_URL, [
            'id' => 1,
            'wallet_type' => WalletBalanceType::WinningsBalance->value,
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Insufficient wallet balance to proceed']);
    }

    public function walletTypeDataProvider()
    {
        return [
            [WalletBalanceType::CreditsBalance->value],
            [WalletBalanceType::BonusBalance->value],
        ];
    }
}