<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChallengeRequest;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use App\Services\Firebase\FirestoreService;


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

    }

    public function test_challenge_scores_computation(): void
    {
        $this->instance(
            FirestoreService::class,
            Mockery::mock(FirestoreService::class, function (MockInterface $mock) {
                $mock->shouldReceive('updateDocument')->once();
            })
        );

        $category = Category::factory()->create();
        $this->seedQuestions($category);

        ChallengeRequest::factory()->create([
            'session_token' => '456ru',
            'challenge_request_id' => '456',
            'status' => 'MATCHED',
            'category_id' => $category->id,
            'amount' => 500,
            'started_at' => now(),
        ]);

        ChallengeRequest::factory()->create([
            'session_token' => '456ru',
            'challenge_request_id' => '123',
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
                    'challenge_request_id' => '123',
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
