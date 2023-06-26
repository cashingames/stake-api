<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\Odds\QuestionsHardeningService;
use UserSeeder;
use CategorySeeder;
use Database\Seeders\GameTypeSeeder;

class QuestionHardenerTest extends TestCase
{
    use RefreshDatabase;

    public $questionHardener;
    public $user;
    public $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->seed(GameTypeSeeder::class);
        $this->user = User::inRandomOrder()->first();
        $this->category = Category::inRandomOrder()->first();
        $this->questionHardener = new QuestionsHardeningService($this->user, $this->category );
    }

    public function testQuestionHardenerDeterminerWorks()
    {
        $questions = $this->questionHardener->determineQuestions(false);

        $this->assertIsObject(
            $questions
        );
    }

//@TODO Delete question hardener totally

// public function testAllQuestionsReturnedBelongToTheSameCategory()
// {
//     Question::factory()
//         ->count(500)
//         ->create();

//     $questions = $this->questionHardener->determineQuestions(false);
//     $questionCategories = [];

//     foreach ($questions as $q) {
//         $questionCategories[] = $q->category_id;
//     }

//     if (empty($questionCategories)) {
//         $this->assertEmpty($questions);
//     }

//     if (array_unique($questionCategories) == array($this->category->id)) {
//         $questionCategory = $this->category->id;
//         $this->assertEquals($this->category->id, $questionCategory);
//     }

//     $this->assertNotEmpty($questionCategories);
// }
   
}
