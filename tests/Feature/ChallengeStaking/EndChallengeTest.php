<?php

namespace Tests\Feature\ChallengeStaking;

use Mockery;
use Tests\TestCase;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Question;
use App\Models\Category;
use Mockery\MockInterface;
use App\Models\ChallengeRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use App\Services\Firebase\FirestoreService;
use App\Jobs\SendChallengeRefundNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EndChallengeTest extends TestCase
{

    use RefreshDatabase;

    const URL = '/api/v3/challenges/submit';

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
                self::URL,
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
                self::URL,
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
            'non_withdrawable' => 500,
        ]);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $secondUser->id,
            'non_withdrawable' => 500,
        ]);

        //assert that refund push notification was queued
        Queue::assertPushed(SendChallengeRefundNotification::class, 2);
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