<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\UserNotification;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);

        $this->user = User::first();
        
        $this->actingAs($this->user);
    }
    public function test_can_get_user_notifications(){
        $response = $this->getJson("/api/v3/notifications");
        $response->assertOk();
        $response->assertJson([
            'message' => "Notifications fetched successfully"
        ]);
    }

    public function test_can_mark_single_notification_as_read(){
        $notification = $this->user->notifications()->create([
            'id' => Str::uuid()->toString(),
            'type' => User::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'data' => [
                'title' => 'title',
                'body' => 'body'
            ]
            ]);

        $response = $this->postJson("/api/v3/notifications/read/{$notification->id}/");
        $response->assertOk();
        $response->assertJson([
            'message' => 'Notification marked as read'
        ]);
        $notification = $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_can_mark_all_notifications_as_read()
    {
        $counter = 0;
        for ($i=0; $i < 4; $i++) {
            $notification = $this->user->notifications()->create([
                'id' => Str::uuid()->toString(),
                'type' => User::class,
                'notifiable_type' => User::class,
                'notifiable_id' => $this->user->id,
                'data' => [
                    'title' => 'title',
                    'body' => 'body'
                ]
            ]);
            $counter = $i;
        }
        
        $response = $this->postJson("/api/v3/notifications/read/all");
        $response->assertOk();
        $response->assertJson([
            'message' => 'Notification marked as read'
        ]);
        $this->assertDatabaseCount('user_notifications', 4);
    }
}
