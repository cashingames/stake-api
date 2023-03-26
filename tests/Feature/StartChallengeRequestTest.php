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
    public function test_challenge_request_returns_request_id(): void
    {   
        $wallet = Wallet::factory()->create();
        $category = Category::factory()->create();

        $wallet->non_withdrawable_balance += 1000;
        $wallet->save();

        $this->actingAs(User::first());
       

        $response = $this->post('/api/v3/challenges/create',[
            'category' => $category->id,
            'amount' => 500
        ]);

        $response->assertStatus(200);
    }
}
