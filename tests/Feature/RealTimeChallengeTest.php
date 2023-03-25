<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Service;
use App\Services\Firebase\RealTimeDatabaseService;
use Mockery\MockInterface;

class RealTimeChallengeTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */

    const CREATE_FIRESTORE_DOCUMENT_URL = '/api/v3/firestore/document/create';

    protected $user, $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        
        $this->user = User::first();
        $this->category = Category::first();

        $this->actingAs($this->user);
        $this->user->wallet->save();
    }

    public function test_that_user_must_have_enough_money_wallet_in_real_time_challenge(): void
    {   
        $response = $this->postjson(self::CREATE_FIRESTORE_DOCUMENT_URL, [
            'category' => $this->category->first()->id,
            'amount' => 500,
        ]);
        $response->assertJson([
            'message' => 'The amount must not be greater than 0.',
        ]);
    }

    public function test_that_category_must_exist_to_be_selected_in_realtime_challenge(): void
    {   
        $response = $this->postjson(self::CREATE_FIRESTORE_DOCUMENT_URL, [
            'category' => 100,
            'amount' => 500,
        ]);
        $response->assertJson([
            'message' => 'The selected category is invalid. (and 1 more error)',
        ]);
    }
}
