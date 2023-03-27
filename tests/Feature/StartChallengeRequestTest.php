<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StartChallengeRequestTest extends TestCase
{
    use RefreshDatabase;
    const API_URL = '/api/v3/challenges/create';

    public function test_challenge_request_returns_sucess(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Wallet::factory()
            ->for($user)
            ->create([
                'non_withdrawable_balance' => 1000
            ]);

        $response = $this->actingAs($user)
            ->post(self::API_URL, [
                'category' => $category->id,
                'amount' => 500
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('challenge_requests', [
            'category_id' => $category->id,
            'amount' => 500,
            'user_id' => $user->id,
            'username' => $user->username
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'non_withdrawable_balance' => 500,
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $user->wallet->id,
            'amount' => 500,
        ]);
    }

    public function test_challenge_request_returns_error_when_user_has_insufficient_balance(): void
    {
        $user = User::factory()->create();

        Wallet::factory()
            ->for($user)
            ->create([
                'non_withdrawable_balance' => 1000
            ]);

        $response = $this->actingAs($user)
            ->postJson(self::API_URL, [
                'category' => 4567,
                'amount' => 150000
            ]);

        $response->assertStatus(422);
    }
}
