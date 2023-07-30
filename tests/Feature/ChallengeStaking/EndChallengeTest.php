<?php

namespace Tests\Feature\ChallengeStaking;

use App\Enums\WalletBalanceType;
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
use App\Models\TriviaChallengeQuestion;
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

    /**
     * @dataProvider walletTypeDataProvider
     */
    public function test_challenge_draw_flow(string $walletType1, string $walletType2): void
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
            'fund_source' => $walletType1,
        ]);

        ChallengeRequest::factory()->for($secondUser)->create([
            'session_token' => '123',
            'challenge_request_id' => '2',
            'status' => 'MATCHED',
            'category_id' => $category->id,
            'amount' => 500,
            'started_at' => now(),
            'fund_source' => $walletType2,
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
            'non_withdrawable' => $walletType1 == WalletBalanceType::CreditsBalance->value ? 500 : 0,
            'bonus' => $walletType1 == WalletBalanceType::BonusBalance->value ? 500 : 0,
        ]);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $secondUser->id,
            'non_withdrawable' => $walletType2 == WalletBalanceType::CreditsBalance->value ? 500 : 0,
            'bonus' => $walletType2 == WalletBalanceType::BonusBalance->value ? 500 : 0,
        ]);

        //assert that refund push notification was queued
        Queue::assertPushed(SendChallengeRefundNotification::class, 2);
    }

    /**
     * @dataProvider walletTypeDataProvider
     */
    public function test_challenge_win_flow(string $walletType1, string $walletType2)
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

        //seed logged questions
        //find question with correct options
        $question = Question::whereHas('options', function ($query) {
            $query->where('is_correct', true);
        })->first();
        TriviaChallengeQuestion::factory()->create([
            'challenge_request_id' => '1',
            'question_id' => $question->id,
            'option_id' => $question->id,
        ]);

        $this
            ->actingAs(User::first())
            ->postJson(
                self::URL,
                [
                    'challenge_request_id' => '1',
                    'selected_options' => [
                        [
                            'question_id' => $question->id,
                            'option_id' => $question->options->where('is_correct', 1)->first()->id,
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
                    'selected_options' => []
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
            'withdrawable' => 500 * 2,
        ]);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $secondUser->id,
            'withdrawable' => 0,
            'non_withdrawable' => 0,
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $secondUser->id,
            'non_withdrawable' => 0,
            'bonus' => 0,
            'withdrawable' => 0,
        ]);
    }

    private function seedQuestions(Category $category): array
    {
        $questions = Question::factory()
            ->hasOptions(4)
            ->count(20)
            ->create([
                'level' => 'easy',
            ]);

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

    public function walletTypeDataProvider()
    {
        return [
            [WalletBalanceType::CreditsBalance->value, WalletBalanceType::CreditsBalance->value],
            [WalletBalanceType::BonusBalance->value, WalletBalanceType::BonusBalance->value],
        ];
    }
}