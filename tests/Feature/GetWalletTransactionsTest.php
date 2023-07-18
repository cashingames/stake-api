<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WalletTransaction;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetWalletTransactionsTest extends TestCase
{
    use RefreshDatabase;
    const URL = '/api/v3/wallet/transactions';

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        WalletTransaction::factory()
            ->count(5)
            ->create();

        $this->user = User::first();

        $this->actingAs($this->user);
    }

    public function test_that_bonus_wallet_transactions_can_be_fetched(): void
    {
        WalletTransaction::query()->update([
            'wallet_id' => $this->user->wallet->id,
            'balance_type' => 'BONUS_BALANCE'
        ]);

        $response = $this->get(self::URL . '/BONUS_BALANCE');

        $response->assertJsonCount(5, '*');
    }

    public function test_that_deposit_wallet_transactions_can_be_fetched(): void
    {
        WalletTransaction::query()->update([
            'wallet_id' => $this->user->wallet->id,
            'balance_type' => 'CREDIT_BALANCE'
        ]);

        $response = $this->get(self::URL . '/CREDIT_BALANCE');

        $response->assertJsonCount(5, '*');
    }

    public function test_that_winnings_wallet_transactions_can_be_fetched(): void
    {
        WalletTransaction::query()->update([
            'wallet_id' => $this->user->wallet->id,
            'balance_type' => 'WINNINGS_BALANCE'
        ]);

        $response = $this->get(self::URL . '/WINNINGS_BALANCE');

        $response->assertJsonCount(5, '*');
    }
}
