<?php

namespace Tests\Feature\ChallengeStaking;

use Mockery;
use Tests\TestCase;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Category;
use Mockery\MockInterface;
use App\Jobs\MatchChallengeRequest;
use Illuminate\Support\Facades\Queue;
use App\Services\Firebase\FirestoreService;
use App\Jobs\MatchWithHumanChallengeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StartChallengeRequestTest extends TestCase
{
    use RefreshDatabase;
    const URL = '/api/v3/challenges/create';

    public function setUp(): void
    {
        parent::setUp();

        $this->createBotUser();

        $this->withHeader('x-request-env', 'development');
        $this->instance(
            FirestoreClient::class,
            Mockery::mock(FirestoreClient::class)
        );

        config(['trivia.minimum_challenge_staking_amount' => 100]);
        config(['trivia.maximum_challenge_staking_amount' => 1000]);

        Queue::fake();
    }

    public function test_challenge_request_returns_sucess(): void
    {
        $this->instance(
            FirestoreService::class,
            Mockery::mock(FirestoreService::class, function (MockInterface $mock) {
                $mock->shouldReceive('createDocument')->once();
            })
        );

        $category = Category::factory()->create();

        $user = $this->prepareMatchRequest($category, 500);

        $this->assertDatabaseHas('challenge_requests', [
            'category_id' => $category->id,
            'amount' => 500,
            'user_id' => $user->id,
            'username' => $user->username,
            'status' => 'MATCHING',
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'non_withdrawable' => 500,
        ]);
    }

    public function test_challenge_request_returns_error_when_amount_is_less_than_minimum_challenge_amount(): void
    {
        $this->instance(
            FirestoreService::class,
            Mockery::mock(FirestoreService::class)
        );
        $user = User::factory()
            ->hasProfile(1)
            ->create();

        $category = Category::factory()->create();

        Wallet::factory()
            ->for($user)
            ->create([
                'non_withdrawable' => 2000
            ]);

        $response = $this->actingAs($user)
            ->postJson(self::URL, [
                'category' => $category->id,
                'amount' => 50
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            "message" => "The amount must be at least 100."
        ]);
    }

    public function test_challenge_request_returns_error_when_amount_is_more_than_maximum_challenge_amount(): void
    {
        $this->instance(
            FirestoreService::class,
            Mockery::mock(FirestoreService::class)
        );
        $user = User::factory()
            ->hasProfile(1)
            ->create();

        $category = Category::factory()->create();

        Wallet::factory()
            ->for($user)
            ->create([
                'non_withdrawable' => 2000
            ]);

        $response = $this->actingAs($user)
            ->postJson(self::URL, [
                'category' => $category->id,
                'amount' => 1500
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            "message" => "The amount must not be greater than 1000.",
        ]);
    }

    public function test_challenge_request_human_and_bot_matching_jobs_were_pushed(): void
    {
        $this->instance(
            FirestoreService::class,
            Mockery::mock(FirestoreService::class, function (MockInterface $mock) {
                $mock->shouldReceive('createDocument')->once();
            })
        );

        $category = Category::factory()->create();

        $user = $this->prepareMatchRequest($category, 500);

        $this->assertDatabaseHas('challenge_requests', [
            'category_id' => $category->id,
            'amount' => 500,
            'user_id' => $user->id,
            'username' => $user->username,
            'status' => 'MATCHING',
        ]);

        Queue::assertPushed(MatchWithHumanChallengeRequest::class);
        Queue::assertPushed(MatchChallengeRequest::class);
    }
    private function prepareMatchRequest($category, $amount): User
    {
        $user = User::factory()
            ->hasProfile(1)
            ->hasWallet(1, [
                'non_withdrawable' => 1000
            ])
            ->create();

        $this->actingAs($user)
            ->post(self::URL, [
                'category' => $category->id,
                'amount' => $amount
            ]);

        return $user;
    }

    private function createBotUser(): void
    {
        User::factory()
            ->hasProfile(1)
            ->hasWallet(1, [
                'non_withdrawable' => 1000
            ])
            ->create();
    }
}