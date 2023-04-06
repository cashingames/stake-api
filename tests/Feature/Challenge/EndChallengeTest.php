<?php

namespace Tests\Feature\Challenge;

use App\Jobs\SendChallengeRefundNotification;
use App\Models\Category;
use App\Models\ChallengeRequest;
use App\Models\Option;
use App\Models\Question;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use App\Services\Firebase\FirestoreService;
use Illuminate\Support\Facades\Queue;


class EndChallengeTest extends TestCase
{

    use RefreshDatabase;

    const API_URL = '/api/v3/challenges/submit';

    public function setUp(): void
    {
        parent::setUp();

        $this->instance(
            FirestoreClient::class,
            Mockery::mock(FirestoreClient::class, function (MockInterface $mock) {
                $mock->shouldReceive('createDocument')->never();
            })
        );
        User::factory()->has(Wallet::factory())->count(5)->create();
    }

    public function test_challenge_draw_flow(): void
    {
        $this->instance(
            FirestoreService::class,
            Mockery::mock(FirestoreService::class, function (MockInterface $mock) {
                $mock->shouldReceive('updateDocument')->times(4);
            })
        );
        Queue::fake();
        $category = Category::factory()->create();
        $this->seedQuestions($category);

        $firstUser = User::skip(2)->first();
        $secondUser = User::skip(3)->first();

        // dd($firstUser->id);
        ChallengeRequest::factory()->for($firstUser)->create([
            'session_token' => '123',
            'challenge_request_id' => '1',
            'status' => 'MATCHED',
            'category_id' => $category->id,
            'amount' => 500,
            'started_at' => now(),
        ]);

        ChallengeRequest::factory()->for($secondUser)->create([
            'session_token' => '123',
            'challenge_request_id' => '2',
            'status' => 'MATCHED',
            'category_id' => $category->id,
            'amount' => 500,
            'started_at' => now()
        ]);

        $this
            ->actingAs(User::first())
            ->postJson(
                self::API_URL,
                [
                    'challenge_request_id' => '1',
                    'selected_options' => [
                        [
                            'question_id' => 1,
                            'option_id' => 1
                        ]
                    ]
                ]
            )
            ->assertStatus(200);

        $this->assertDatabaseHas('challenge_requests', [
            'challenge_request_id' => '1',
            'status' => 'COMPLETED',
        ]);
        $this->assertDatabaseHas('challenge_requests', [
            'challenge_request_id' => '2',
            'status' => 'MATCHED',
        ]);

        $this
            ->actingAs(User::find(2))
            ->postJson(
                self::API_URL,
                [
                    'challenge_request_id' => '2',
                    'selected_options' => [
                        [
                            'question_id' => '1',
                            'option_id' => '1'
                        ],
                        [
                            'question_id' => '1',
                            'option_id' => '1'
                        ],

                    ]
                ]
            )
            ->assertStatus(200);

        $this->assertDatabaseHas('challenge_requests', [
            'challenge_request_id' => '1',
            'status' => 'COMPLETED',
        ]);
        $this->assertDatabaseHas('challenge_requests', [
            'challenge_request_id' => '2',
            'status' => 'COMPLETED',
        ]);

        //refund if both users got the same score
        $this->assertDatabaseHas('wallets', [
            'user_id' => $firstUser->id,
            'non_withdrawable_balance' => 500,
        ]);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $secondUser->id,
            'non_withdrawable_balance' => 500,
        ]);

        //assert that refund push notification was queued
        Queue::assertPushed(SendChallengeRefundNotification::class, 2);
    }

    public function test_challenge_bot_flow(): void
    {
        $this->instance(
            FirestoreService::class,
            Mockery::mock(FirestoreService::class, function (MockInterface $mock) {
                $mock->shouldReceive('updateDocument')->times(4);
            })
        );

        $category = Category::factory()->create();
        $this->seedQuestions($category);

        $firstUser = User::skip(2)->first();
        $secondUser = User::first();

        ChallengeRequest::factory()->for($firstUser)->create([
            'session_token' => '123',
            'challenge_request_id' => '1',
            'status' => 'MATCHED',
            'category_id' => $category->id,
            'amount' => 500,
            'started_at' => now(),
        ]);

        ChallengeRequest::factory()->for($secondUser)->create([
            'session_token' => '123',
            'challenge_request_id' => '2',
            'status' => 'MATCHED',
            'category_id' => $category->id,
            'amount' => 500,
            'started_at' => now()
        ]);

        $this
            ->actingAs(User::first())
            ->postJson(
                self::API_URL,
                [
                    'challenge_request_id' => '1',
                    'selected_options' => [
                        [
                            'question_id' => 1,
                            'option_id' => 1
                        ]
                    ]
                ]
            )
            ->assertStatus(200);

        $this
            ->actingAs(User::find(2))
            ->postJson(
                self::API_URL,
                [
                    'challenge_request_id' => '2',
                    'selected_options' => [
                        [
                            'question_id' => '1',
                            'option_id' => '1'
                        ],
                        [
                            'question_id' => '1',
                            'option_id' => '1'
                        ],

                    ]
                ]
            )
            ->assertStatus(200);

        $this->assertDatabaseHas('challenge_requests', [
            'challenge_request_id' => '1',
            'status' => 'COMPLETED',
        ]);
        $this->assertDatabaseHas('challenge_requests', [
            'challenge_request_id' => '2',
            'status' => 'COMPLETED',
        ]);

    }

    private function seedQuestions(Category $category): array
    {
        $questions = Question::factory()
            ->hasOptions(4)
            ->count(250)
            ->create();

        $data = [];

        foreach ($questions as $question) {
            $data[] = [
                'question_id' => $question->id,
                'category_id' => $category->id
            ];
        }

        DB::table('categories_questions')->insert($data);

        return $data;
    }

}
