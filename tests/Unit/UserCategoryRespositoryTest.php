<?php

namespace Tests\Unit;

use App\Actions\UserCategories\AddCategoriesAction;
use App\Actions\UserCategories\RemoveCategoriesAction;
use App\Models\Category;
use App\Models\User;
use App\Models\UserCategory;
use App\Repositories\UserCategoryRespository;
use Database\Seeders\CategorySeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserCategoryRespositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user; protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->user = User::first();
        $this->category = Category::first();
        $this->actingAs($this->user);
    }
    
    public function test_that_categories_are_added()
    {
        $userCategoryRespository = $this->createMock(UserCategoryRespository::class);
        $userCategoryRespository->expects($this->once())
        ->method('addCategory')
        ->willReturn(true);

        $categoryId = $this->category->id;
        $userId = $this->user->id;
        $addCategory = new AddCategoriesAction($userCategoryRespository);
       $response = $addCategory->execute($userId, $categoryId);
       $this->assertTrue($response);
    }

    public function test_that_categories_can_be_deleted()
    {
        $userCategoryRespository = $this->createMock(UserCategoryRespository::class);
        $userCategoryRespository->expects($this->once())
        ->method('removeCategory')
        ->willReturn(true);

        $categoryId = $this->category->id;
        $userId = $this->user->id;
        $removeCategory = new RemoveCategoriesAction($userCategoryRespository);
       $response = $removeCategory->execute($categoryId);
       $this->assertTrue($response);
    }
}
