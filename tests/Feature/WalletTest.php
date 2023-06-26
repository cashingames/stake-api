<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Database\Seeders\UserSeeder;
use App\Models\User;

class WalletTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    protected $user;

    const BASE_URL = '/api/v3/wallet/me/transactions';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->user = User::first();

        $this->actingAs($this->user);
    }

    public function test_transactions_can_be_gotten()
    {
        $response = $this->get(self::BASE_URL);
        $response->assertStatus(200);
    }

    public function test_earnings_transactions_can_be_gotten()
    {
        $response = $this->get(self::BASE_URL . '/earnings');
        $response->assertStatus(200);
    }

}
