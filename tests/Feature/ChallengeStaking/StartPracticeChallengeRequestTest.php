<?php

namespace Tests\Feature\ChallengeStaking;

use App\Enums\GameRequestMode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Category;
use Mockery\MockInterface;
use Illuminate\Support\Facades\Queue;
use App\Services\Firebase\FirestoreService;
use App\Jobs\MatchWithPracticeBotChallengeRequest;


class StartPracticeChallengeRequestTest extends TestCase
{
    use RefreshDatabase;
    const URL = '/api/v3/challenges/practice/create';

    public function setUp(): void
    {
        parent::setUp();

        $this->withHeader('x-request-env', 'development');
        $this->instance(
            FirestoreClient::class,
            Mockery::mock(FirestoreClient::class)
        );
        Queue::fake();
    }

    public function test_challenge_request_is_created_with_practice_mode(): void
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
            'request_mode' => GameRequestMode::CHALLENGE_PRACTICE->value
        ]);
    }

    public function test_challenge_request_bot_matching_job_was_pushed(): void
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

        Queue::assertPushed(MatchWithPracticeBotChallengeRequest::class);
    }

    public function test_challenge_request_with_practice_mode_does_not_deduct_from_wallet_balance(): void
    {
        $this->instance(
            FirestoreService::class,
            Mockery::mock(FirestoreService::class, function (MockInterface $mock) {
                $mock->shouldReceive('createDocument')->once();
            })
        );

        $category = Category::factory()->create();

        $user = $this->prepareMatchRequest($category, 500);

        $this->assertDatabaseHas('wallets', [
            'id' => $user->wallet->id,
            'user_id' => $user->id,
            'non_withdrawable' => 1000,
            'bonus' => 0.00,
        ]);
    }

    private function prepareMatchRequest($category, $amount): User
    {
        $user = User::factory()
            ->hasProfile(1)
            ->create();

        Wallet::factory()
            ->for($user)
            ->create([
                'non_withdrawable' => 1000
            ]);
        $this->actingAs($user)
            ->post(self::URL, [
                'category' => $category->id,
                'amount' => $amount
            ]);

        return $user;
    }

}
