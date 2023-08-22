<?php

namespace Tests\Unit;

use App\Jobs\SendCashdropDroppedNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendCashdropDroppedNotificationJobTest extends TestCase
{
   use RefreshDatabase;
   public $user;
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'phone_verified_at'  => now(),
            'last_activity_time' => now()
        ]);
    }

    public function test_send_cashdrop_winner_notification(): void
    {
        
        $job = new SendCashdropDroppedNotification( $this->user->username,200,'Silver');
    
        $job->handle();

        $this->assertDatabaseHas('user_notifications', [
            'notifiable_id' => $this->user->id,
            'notifiable_type' =>  "App\\Models\\User",
        ]);
    }

}
