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
    /**
     * A basic feature test example.
     */


    private $wallet;
    private $category;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wallet = Wallet::factory()->create();
        $this->category = Category::factory()->create();
        $this->user =  User::first();
        $this->wallet->non_withdrawable_balance += 1000;
        $this->wallet->save();
        $this->actingAs($this->user);
    }

    public function test_challenge_request_returns_request_id(): void
    {

        $response = $this->post('/api/v3/challenges/create', [
            'category' => $this->category->id,
            'amount' => 500
        ]);

        $response->assertStatus(200);
    }

    public function test_that_a_challenge_request_record_is_created(): void
    {

        $response = $this->post('/api/v3/challenges/create', [
            'category' => $this->category->id,
            'amount' => 500
        ]);

        $this->assertDatabaseHas('challenge_requests',[
            'category_id' => $this->category->id,
            'amount' => 500,
            'user_id' => $this->user->id,
            'username' => $this->user->username
        ]);
    }
}
