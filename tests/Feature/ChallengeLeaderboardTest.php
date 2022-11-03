<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Challenge;
use App\Models\ChallengeGameSession;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ChallengeSeeder;
use Database\Seeders\GameModeSeeder;
use Database\Seeders\GameTypeSeeder;
use Database\Seeders\UserSeeder;

class ChallengeLeaderboardTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    protected $user;
    protected $category;
    protected $challenge;
    protected $userChallengeGameSession;
    protected $opponentChallengeGameSession;
    protected $opponent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        // $this->seed(CategorySeeder::class);


        $this->seed(GameTypeSeeder::class);
        $this->seed(GameModeSeeder::class);
        $this->seed(ChallengeSeeder::class);
        ChallengeGameSession::factory()
            ->count(5)
            ->create();

        $this->user = User::first();
        $this->category = Category::where('category_id', '!=', 0)->inRandomOrder()->first();
        $this->challenge = Challenge::first();
        $this->userChallengeGameSession = ChallengeGameSession::inRandomOrder()->first();
        $this->userChallengeGameSession->update([
            'user_id' => $this->user->id,
            'challenge_id' => $this->challenge->id
        ]);
        $this->opponentChallengeGameSession = ChallengeGameSession::where('user_id', '!=', $this->user->id)->inRandomOrder()->first();

        $this->opponentChallengeGameSession->update([
            'challenge_id' => $this->challenge->id
        ]);
        $this->opponent =  User::find($this->opponentChallengeGameSession->user_id);
        $this->challenge->update([
            'user_id' => $this->user->id,
            'opponent_id' =>  $this->opponent->id
        ]);
        $this->actingAs($this->user);
    }

    // public function test_a_challenge_leaderboard_can_be_fetched()
    // {
    //     $this->userChallengeGameSession->update([
    //         'points_gained' => 8,
    //     ]);

    //     $response = $this->get("/api/v3/challenge/" . $this->challenge->id . "/leaderboard");
    //     $response->assertJson([
    //         "challengerUsername" => $this->user->username,
    //         "opponentUsername" => $this->opponent->username,
    //     ]);
    // }

    public function test_global_challenge_leaders_can_be_gotten()
    {
        $response = $this->post("/api/v3/challenge/leaders/global");
        $response->assertStatus(200);
        
    }
}
