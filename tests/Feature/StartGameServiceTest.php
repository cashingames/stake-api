<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PlayGame\LiveTriviaGameService;
use App\Services\PlayGame\StandardExhibitionGameService;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StartGameServiceTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->actingAs(User::first());
    }

    public function testThatANewUserExhibitionLastThreeGamesAverageisZero()
    {

        $startExhibitionGameService = new StandardExhibitionGameService();
        $result =  $startExhibitionGameService->getAverageOfLastThreeGames();

        $this->assertEquals($result, 0.0);
    }

    public function testThatANewUserLiveTriviaLastThreeGamesAverageisZero()
    {

        $startLiveTriviaGameService = new LiveTriviaGameService();
        $result =  $startLiveTriviaGameService->getAverageOfLastThreeGames();

        $this->assertEquals($result, 0.0);
    }
}
