<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserNotification;
use Database\Seeders\UserNotificationSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(UserNotificationSeeder::class);

        $this->user = User::first();

        $this->actingAs($this->user);
    }

    public function test_can_get_user_notifications_if_no_brand_id_is_set()
    {

        $response = $this->getJson("/api/v3/notifications");

        $response->assertJsonCount(5, 'data.data');
    }

    public function test_can_get_user_notifications_if_a_brand_id_is_set()
    {

        $response = $this->withHeaders([
            'x-brand-id' => 3,
        ])->getJson("/api/v3/notifications");

        $response->assertJsonCount(5, 'data.data');
    }

    public function test_can_mark_single_notification_as_read()
    {
        $notification = UserNotification::first();
        $response = $this->postJson("/api/v3/notifications/read/" . $notification->id);
        $response->assertOk();
        $notification = $notification->refresh();

        $this->assertNotNull($notification->read_at);
    }

    public function test_can_mark_all_notifications_as_read()
    {

        $response = $this->postJson("/api/v3/notifications/read/all");
        $response->assertOk();
        $this->assertDatabaseCount('user_notifications', 5);

        $readCount = 0;

        foreach ($this->user->notifications as $notification) {
            if (!is_null($notification->read_at)) {
                $readCount += 1;
            }
        }
        $this->assertEquals($readCount, 5);
    }

    public function test_that_challenge_notifications_are_not_returned_for_staking_platform()
    {
        $response = $this->withHeaders([
            'x-brand-id' => 2,
        ])->getJson("/api/v3/notifications");

        $response->assertJsonCount(0, 'data.data');
    }
}
