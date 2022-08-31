<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\Odds\QuestionsHardeningService;
use UserSeeder;
use CategorySeeder;



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
        $this->user = User::inRandomOrder()->first();
        $this->category = Category::inRandomOrder()->first();
        $this->questionHardener = new QuestionsHardeningService();
    }
    
    public function testQuestionHardenerDeterminerWorks()
    {
        $questions = $this->questionHardener->determineQuestions($this->user , $this->category);

        $this->assertIsObject(
            $questions
        );
          
    }
}