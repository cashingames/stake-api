<?php

namespace Tests\Feature;

use CategorySeeder;
use App\Models\Category;
use App\Models\Question;
use Database\Seeders\GameModeSeeder;
use Database\Seeders\GameTypeSeeder;
use Database\Seeders\QuestionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

use function PHPUnit\Framework\assertCount;

class CategoryQuestionRelationshipTest extends TestCase
{   
    use RefreshDatabase;

    protected $category;
    protected $categories;
    protected $questions;
    protected $question;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);
        $this->seed(GameTypeSeeder::class);
        $this->seed(GameModeSeeder::class);
        $this->seed(QuestionSeeder::class);
       
        $this->category = Category::subcategories()->inRandomOrder()->first();
        $this->categories = Category::subcategories()->inRandomOrder()->limit(3)->get();
        $this->questions = Question::inRandomOrder()->limit(5)->get();
        $this->question = Question::inRandomOrder()->first();
    }
    
    public function test_that_a_category_can_belong_to_many_questions(){

        foreach($this->questions as $question){
            DB::table('categories_questions')->insert([
                'category_id' => $this->category->id,
                'question_id' => $question->id
            ]);
        }
        
        $categoryQuestions = Category::find($this->category->id)->questions()->get();
    
        assertCount(3, $categoryQuestions);
    }

    public function test_that_a_question_can_belong_to_many_categories(){

        foreach($this->categories as $category){
            DB::table('categories_questions')->insert([
                'category_id' => $category->id,
                'question_id' => $this->question->id
            ]);
        }
        
        $questionCategories = Question::find($this->question->id)->categories()->get();
    
        assertCount(3, $questionCategories);
    }

    
}
