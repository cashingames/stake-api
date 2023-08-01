<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Question;
use App\Models\User;
use Tests\TestCase;
use App\Repositories\Cashingames\TriviaQuestionRepository;
use App\Repositories\Cashingames\WalletRepository;
use App\Services\StakeQuestionsHardeningService;
use Database\Seeders\CategorySeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class StakeQuestionsHardeningServiceTest extends TestCase
{
    use RefreshDatabase;

    public $user , $category;
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
       
        $this->user = User::first();
        $this->category = Category::first();

        $this->actingAs($this->user);

        $questions = Question::factory()
            ->count(50)
            ->create();

        $data = [];

        foreach ($questions as $question) {
            $data[] = [
                'question_id' => $question->id,
                'category_id' => $this->category->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('categories_questions')->insert($data);
    }

    public function test_that_hard_and_medium_questions_can_be_fetched_based_on_user_profit()
    {   
        $mockedTriviaQuestionRepository = $this->mockTriviaQuestionRepository();
        $mockedTriviaQuestionRepository
            ->expects($this->once())
            ->method('getRandomHardAndMediumQuestionsWithCategoryId')
            ->with($this->category->id)
            ->willReturn(new Collection());

        $mockedWalletRepository = $this->mockWalletRepository();
        $mockedWalletRepository
            ->expects($this->once())
            ->method('getUserProfitPercentageOnStakingThisYear')
            ->with($this->user->id)
            ->willReturn(35);

        $_service = new StakeQuestionsHardeningService(
            $mockedTriviaQuestionRepository,
            $mockedWalletRepository,
        );

        $result =  $_service->determineQuestions($this->user->id, $this->category->id);

        $this->assertEquals($result , new Collection);
        
    }

    public function test_that_easy_questions_can_be_fetched_based_on_user_profit()
    {   
        $mockedTriviaQuestionRepository = $this->mockTriviaQuestionRepository();
        $mockedTriviaQuestionRepository
            ->expects($this->once())
            ->method('getRandomEasyQuestionsWithCategoryId')
            ->with($this->category->id)
            ->willReturn(new Collection());

        $mockedWalletRepository = $this->mockWalletRepository();
        $mockedWalletRepository
            ->expects($this->once())
            ->method('getUserProfitPercentageOnStakingThisYear')
            ->with($this->user->id)
            ->willReturn(10);

        $_service = new StakeQuestionsHardeningService(
            $mockedTriviaQuestionRepository,
            $mockedWalletRepository,
        );

        $result =  $_service ->determineQuestions($this->user->id, $this->category->id);

        $this->assertEquals($result , new Collection);
        
    }
    private function mockTriviaQuestionRepository()
    {
        return $this->createMock(TriviaQuestionRepository::class);
    }

    private function mockWalletRepository()
    {
        return $this->createMock(WalletRepository::class);
    }
}
