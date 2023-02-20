<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\UserNotification;
use App\Notifications\ChallengeStatusUpdateNotification;
use Database\Seeders\UserNotificationSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\WithFaker;
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

    /**
     * Test the excepted no of notifications that's returned from the DB 
     * 
     * @return void
     */
    public function test_can_get_user_notifications(){

        $response = $this->getJson("/api/v3/notifications");
        
        $response->assertJsonCount(5, 'data.data');
    }

    public function test_can_mark_single_notification_as_read(){
        // $notification = $this->user->notifications()->create([
        //     'id' => Str::uuid()->toString(),
        //     'type' => User::class,
        //     'notifiable_type' => User::class,
        //     'notifiable_id' => $this->user->id,
        //     'data' => [
        //         'title' => 'title',
        //         'body' => 'body'
        //     ]
        //     ]);

        $notification = UserNotification::first();
        $response = $this->postJson("/api/v3/notifications/read/".$notification->id);
        $response->assertOk();
        $notification = $notification->refresh();
        
        $this->assertNotNull($notification->read_at);
    }

    // public function test_can_mark_all_notifications_as_read()
    // {

    //     /**
    //      * Refractor this to use the normal seeder or factory
    //      */

    //     for ($i=0; $i < 4; $i++) {
    //         $this->user->notifications()->create([
    //             'id' => Str::uuid()->toString(),
    //             'type' => User::class,
    //             'notifiable_type' => User::class,
    //             'notifiable_id' => $this->user->id,
    //             'data' => [
    //                 'title' => 'title',
    //                 'body' => 'body'
    //             ]
    //         ]);
    //     }
        
    //     $response = $this->postJson("/api/v3/notifications/read/all");
    //     $response->assertOk();
    //     $this->assertDatabaseCount('user_notifications', 4);
    // }

/**
 * @TODO Test that if the request comes from the staking mobile web, challenge
 * notification should not be incldued in the response
 */
}
