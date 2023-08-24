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

    protected $user;  protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->user = User::first();
        $this->category = Category::first();
        $this->actingAs($this->user);
    }
    /**
     * A basic feature test example.
     */
    public function test_that_user_can_add_category()
    {
        $data = [$this->category->id];
        $response = $this->post('/api/v3/trivia-quest/add-categories', [
            'data' => $data,
        ]);
            $this->assertDatabaseHas('user_categories', [
                'user_id' => $this->user->id,
                'category_id' => $this->category->id,
            ]);
        $response->assertStatus(200);
    }

    public function test_that_user_can_remove_category()
    {
        $data = [101, 501];
        foreach ($data as $item) {
            UserCategory::create([
                'user_id' => $this->user->id,
                'category_id' => $item,
            ]);
        }
        $this->post('/api/v3/trivia-quest/remove-categories', [
            'data' => [501]
        ]);

        $this->assertDatabaseHas('user_categories', [
            'category_id' => 101,
        ]);

        $this->assertDatabaseMissing('user_categories', [
            'category_id' => 501,
        ]);
    }
}
