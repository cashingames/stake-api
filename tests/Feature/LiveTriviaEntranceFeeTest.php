<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\UserSeeder;
use App\Models\User;
use App\Models\Trivia;
use Database\Seeders\CategorySeeder;
use Database\Seeders\TriviaSeeder;


class LiveTriviaEntranceFeeTest extends TestCase
{
    use RefreshDatabase;

    const LIVE_TRIVIA_ENTRANCE_PAYMENT_URL = '/api/v3/live-trivia/entrance/pay';

    protected $user, $trivia;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->seed(TriviaSeeder::class);
        $this->user = User::first();
        $this->trivia = Trivia::inRandomOrder()->first();
        $this->trivia->update(['entry_fee' => 100]);
        $this->actingAs($this->user);
    }

    public function test_a_user_can_pay_for_a_live_trivia()
    {   
        $this->user->wallet->update(['non_withdrawable' => 500]);

        $response = $this->postjson(self::LIVE_TRIVIA_ENTRANCE_PAYMENT_URL, [
            "liveTriviaId" => $this->trivia->id
        ]);


        $response->assertJson([
            'message' => 'Payment successful',
        ]);
    }

    public function test_a_user_cannot_pay_for_a_live_trivia_with_insufficient_balance()
    {   
        $response = $this->postjson(self::LIVE_TRIVIA_ENTRANCE_PAYMENT_URL, [
            "liveTriviaId" => $this->trivia->id
        ]);


        $response->assertJson([
            'message' => 'Insufficient Wallet Balance',
        ]);
    }

    public function test_that_a_payment_record_is_created_for_user_and_live_trivia()
    {   
        $this->user->wallet->update(['non_withdrawable' => 500]);

        $response = $this->postjson(self::LIVE_TRIVIA_ENTRANCE_PAYMENT_URL, [
            "liveTriviaId" => $this->trivia->id
        ]);

        $this->assertDatabaseHas('live_trivia_user_payments', [
            'trivia_id' => $this->trivia->id,
            'user_id' => $this->user->id
        ]);
    }

}
