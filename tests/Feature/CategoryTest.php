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

    public function test_category_icon_can_be_uploaded()
    {
        Storage::fake('icons');

        $file = UploadedFile::fake()->image('category_icon.png');

        $response = $this->post('/api/v3/category/icon/save', [
            'categoryName' => $this->category->name,
            'icon' => $file,
        ]);

        $response->assertStatus(200);

        // Delete the uploaded image
        Storage::disk('icons')->delete($file->hashName());

    }

}
