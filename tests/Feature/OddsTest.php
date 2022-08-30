<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OddsTest extends TestCase
{
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
    public function test_that_standard_odds_can_be_fetched()
    {
        $response = $this->get('/api/v3/odds/standard');

        $response->assertJson([
            'message' => 'standard odds fetched',
        ]);
    }
}
