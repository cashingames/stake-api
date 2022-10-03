<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Staking;
use App\Models\Category;
use App\Models\Challenge;
use App\Enums\FeatureFlags;
use App\Mail\ChallengeInvite;
use App\Services\FeatureFlag;
use Database\Seeders\UserSeeder;
use App\Models\ChallengeGameSession;
use Database\Seeders\CategorySeeder;
use Database\Seeders\GameModeSeeder;
use Database\Seeders\GameTypeSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Notifications\ChallengeReceivedNotification;

class ChallengeGameTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    const SEND_CHALLENGE_INVITE_URL = "/api/v3/challenge/send-invite";
    const END_CHALLENGE_URL = "/api/v3/challenge/end/game";

    protected $user;
    protected $category;
    
    protected $staking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        
        
        $this->seed(GameTypeSeeder::class);
        $this->seed(GameModeSeeder::class);
        
        $this->user = User::first();
        $this->category = Category::where('category_id', '!=', 0)->inRandomOrder()->first();
        
        $this->actingAs($this->user);
        
        
    }

    public function test_challenge_invite_sent_with_staking_successfully()
    {
        FeatureFlag::enable(FeatureFlags::CHALLENGE_GAME_STAKING);
        Mail::fake();
        Notification::fake();

        $this->user->wallet()->update([
            'non_withdrawable_balance' => 2500,
        ]);

        $player = $this->user;
        $opponent = User::where('id', '<>', $player->id)->first();

        
        $category = $this->category;
        $amountToStake = 1500;

        $response = $this->postJson(self::SEND_CHALLENGE_INVITE_URL, [
            'opponentId' => $opponent->id,
            'categoryId' => $category->id,
            'staking_amount' => $amountToStake
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('challenges', [
            'category_id' => $category->id,
            'user_id' => $player->id,
            'opponent_id' => $opponent->id
        ]);

        $this->assertDatabaseHas('stakings', [
            'amount_staked' => $amountToStake,
            'user_id' => $player->id
        ]);

        $staking = Staking::first();
        
        $challenge = Challenge::where('user_id', $player->id)
         ->where('opponent_id', $opponent->id)
         ->where('category_id', $category->id)
         ->first();

        $this->assertDatabaseHas('challenge_stakings', [
            'challenge_id' => $challenge->id,
            'staking_id' => $staking->id
        ]);
        Mail::assertQueued(ChallengeInvite::class);
        Notification::assertSentTo($opponent, ChallengeReceivedNotification::class);
        
    }
}
