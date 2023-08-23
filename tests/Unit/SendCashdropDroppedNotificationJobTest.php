<?php

namespace Tests\Unit;

use App\Jobs\SendCashdropDroppedNotification;
use App\Models\Cashdrop;
use App\Models\CashdropRound;
use App\Models\GameSession;
use App\Models\User;
use Database\Seeders\CashDropSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendCashdropDroppedNotificationJobTest extends TestCase
{
    use RefreshDatabase;
    public $user, $cashdropRound, $cashdrop;
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
        $this->user = User::first();
        GameSession::factory()
            ->create([
                'user_id' =>  $this->user->id,
                'created_at' => now()
            ]);
        $this->seed(CashDropSeeder::class);
        $this->cashdrop = Cashdrop::first();
        $this->cashdropRound = CashdropRound::create([
            'cashdrop_id' => $this->cashdrop->id,
            'pooled_amount' => 100,
            'dropped_at' => null,
            'percentage_stake' => $this->cashdrop->percentage_stake,
            'created_at' => now()->subDays(2),
            'updated_at' => now()
        ]);
    }

    public function test_send_cashdrop_winner_notification(): void
    {

        $job = new SendCashdropDroppedNotification(
            $this->user->username,
            $this->cashdropRound
        );

        $job->handle();

        $this->assertDatabaseHas('user_notifications', [
            'notifiable_id' => $this->user->id,
            'notifiable_type' =>  "App\\Models\\User",
        ]);
    }
}
