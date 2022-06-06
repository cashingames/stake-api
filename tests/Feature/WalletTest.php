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

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->user = User::first();

        $this->actingAs($this->user);
    }

    public function test_transactions_can_be_gotten()
    {
        $response = $this->get('/api/v2/wallet/me/transactions');
        $response->assertStatus(200);
    }

    public function test_earnings_transactions_can_be_gotten()
    {
        $response = $this->get('/api/v2/wallet/me/transactions/earnings');
        $response->assertStatus(200);
    }

}
