<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\UserCategory;
use Database\Seeders\CategorySeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserCategoryTest extends TestCase
{

    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->user = User::first();
        $this->actingAs($this->user);
    }
    /**
     * A basic feature test example.
     */
    public function test_that_user_can_add_category()
    {

        $data = ["Football", "Music"];
        $response = $this->post('/api/v3/trivia-quest/categories', [
            'data' => $data,
        ]);

        foreach ($data as $item) {
            $category = Category::where('name', $item)->first();
            $this->assertDatabaseHas('user_categories', [
                'user_id' => $this->user->id,
                'category_id' => $category->id,
            ]);
        }

        $response->assertStatus(200);
    }

    public function test_that_user_can_remove_category()
    {
        $data = ["Football", "Music"];
        foreach ($data as $item) {
            $category = Category::where('name', $item)->first();
            UserCategory::create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
            ]);
        }
        $response = $this->post('/api/v3/trivia-quest/remove-categories', [
            'data' => ["Music"]
        ]);

        $football = Category::where('name', 'Football')->first();
       $music = Category::where('name', 'Music')->first();

        $this->assertDatabaseHas('user_categories', [
            'category_id' => $football->id,
        ]);

        $this->assertDatabaseMissing('user_categories', [
            'category_id' => $music->id,
        ]);
    }
}
