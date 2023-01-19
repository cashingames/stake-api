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

    protected $user, $contest;
    // const GET_ALL_CONTEST_URL = '/api/v3/contests/get';
    // const GET_A_SINGLE_CONTEST_URL = '/api/v3/contest/.'{id}/get';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(ContestSeeder::class);

        $this->user = User::first();
        $this->contest = Contest::first();

        $this->actingAs($this->user);
    }


    public function test_all_contests_can_be_gotten()
    {
        $response = $this->get('/api/v3/contests/get');

        $response->assertJsonCount(5, 'data');
        $response->assertStatus(200);
    }

    public function test_all_contests_can_be_gotten_with_their_prize_pools()
    {
        $response = $this->get('/api/v3/contests/get');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    "id",
                    "name",
                    "description",
                    "displayName",
                    "startDate",
                    "endDate",
                    "contestType",
                    "entryMode",
                    "winning_prize_pools" => [],
                ]
            ]
        ]);
        $response->assertStatus(200);

    }

    public function test_a_single_contest_can_be_gotten()
    {
        $response = $this->get('/api/v3/contest/'.$this->contest->id.'/get');

        $response->assertStatus(200);
    }
}
