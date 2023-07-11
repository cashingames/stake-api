<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetBonusOddsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_that_bonus_odds_can_be_fetched()
    {
        config(['bonusOdds' => [
            [
                'id' => 1,
                'score' => 10,
                'odd' => 5
            ],
            [
                'id' => 2,
                'score' => 9,
                'odd' => 0
            ],
        ]]);


        $response = $this->get('/api/v3/odds/bonus');

        $response->assertJson([
            'data' => [],
        ]);
    }
}
