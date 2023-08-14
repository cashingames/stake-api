<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Database\Seeders\CashDropSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GetCashDropDataTest extends TestCase
{
    use RefreshDatabase;
    public $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->has(Profile::factory())->create();
        $this->actingAs($this->user);
        $this->seed(CashDropSeeder::class);
    }

    public function test_that_cashdrop_data_can_be_fetched()
    {
        DB::table('cashdrop_rounds')->insert(
            [
                'cashdrop_id' => 1,
                'pooled_amount' => 1500,
                'dropped_at' => null,
                'percentage_stake' => 0.05,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        DB::table('cashdrop_users')->insert(
            [
                'user_id' => 1,
                'cashdrop_round_id' => 1,
                'winner' => true,
                'amount' => 2000,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        $response = $this->get('/api/v3/cashdrop/data');

        $response->assertJsonCount(2, '*');
    }
}
