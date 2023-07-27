<?php

namespace Tests\Feature;

use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CategoryTest extends TestCase
{
    use RefreshDatabase;
    
    public $category ;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);
        $this->category = Category::first();
    }

    public function test_category_icon_must_be_a_file_to_be_uploaded()
    {
        
        $response = $this->post('/api/v3/category/icon/save', [
            'categoryName' => $this->category->name,
            'icon' => "file",
        ]);

        $response->assertStatus(302);

    }

    public function test_category_name_must_be_a_strings_to_be_uploaded()
    {
        
        $response = $this->post('/api/v3/category/icon/save', [
            'categoryName' => 122333,
            'icon' => "file",
        ]);

        $response->assertStatus(302);

    }

}
