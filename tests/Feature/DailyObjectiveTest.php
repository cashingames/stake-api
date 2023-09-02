<?php

namespace Tests\Feature;

use App\Models\DailyObjective;
use App\Models\User;
use App\Services\DailyObjectiveService;
use Database\Seeders\ObjectiveSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DailyObjectiveTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
        $this->seed(ObjectiveSeeder::class);
        $this->user = User::first();
        $this->actingAs($this->user);
    }

    public function test_that_endpoint_is_successful_when_called()
    {
        $showDailyObjective = new DailyObjectiveService();
        $showDailyObjective->dailyObjective($this->user);
        $response =  $this->get('/api/v3/trivia-quest/daily-objectives');
        $response->assertStatus(200);
    }
}
