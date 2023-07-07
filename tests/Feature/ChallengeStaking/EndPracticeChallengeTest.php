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
use App\Models\TriviaChallengeQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EndPracticeChallengeTest extends TestCase
{

    use RefreshDatabase;

    const URL = '/api/v3/challenges/practice/submit';

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

 
    public function test_practice_challenge_does_not_credit_balance(): void
    {
        $this->instance(
            FirestoreService::class,
            Mockery::mock(FirestoreService::class, function (MockInterface $mock) {
                $mock->shouldReceive('updateDocument')->times(2);
            })
        );

        $category = Category::factory()->create();
        $this->seedQuestions($category);

        $firstUser = User::skip(2)->first();
       
        ChallengeRequest::factory()->for($firstUser)->create([
            'session_token' => '123',
            'challenge_request_id' => '1',
            'status' => 'MATCHED',
            'category_id' => $category->id,
            'amount' => 500,
            'started_at' => now(),
        ]);

        ChallengeRequest::factory()->for(User::first())->create([
            'session_token' => '123',
            'challenge_request_id' => '2',
            'status' => 'MATCHED',
            'category_id' => $category->id,
            'amount' => 500,
            'started_at' => now(),
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
            ->actingAs($firstUser)
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
            'status' => 'COMPLETED',
        ]);

    
        $this->assertDatabaseMissing('wallets', [
            'user_id' => $firstUser->id,
            'withdrawable' => 500 * 2,
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


}