<?php

namespace Tests\Feature;

use App\Models\Contest;
use App\Models\User;
use Database\Seeders\ContestSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContestsTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(ContestSeeder::class);

        $this->actingAs(User::first());
    }


    public function test_all_contests_can_be_gotten()
    {
        $response = $this->get('/api/v3/contests');

        $response->assertJsonCount(5, '*');
        $response->assertStatus(200);
    }

    public function test_all_contests_can_be_gotten_with_their_prize_pools()
    {
        $response = $this->get('/api/v3/contests');

        $response->assertJsonStructure([
            [

                "id",
                "name",
                "description",
                "displayName",
                "startDate",
                "endDate",
                "contestType",
                "entryMode",
                "prizePool" => [],

            ]
        ]);
        $response->assertStatus(200);
    }

    public function test_a_single_contest_can_be_gotten()
    {
        $response = $this->get('/api/v3/contest/' . Contest::first()->id);

        $response->assertJsonCount(9, '*');
        $response->assertStatus(200);
    }
}
