<?php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\TestCase;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public $user;

    private const NOTIFICATION_URL = '/api/v3/notifications';

    protected function setUp(): void
    {
        parent::setUp();

        /**
         * By making use of factory we are really in control of the data
         * we are seeding. This is a good practice.
         *
         * E.g here we are able to say:
         * 1. Create a user
         * 2. Create 5 notifications for the user
         *
         * Previously
         * 1. Create 5 random users from using UserSeeder which was harded there
         * 2. Create 5 random notifications for hard coded user id of 1 in the NotificationSeeder
         */
        $this->user = User::factory()
            ->has(
                UserNotification::factory()->state(
                    new Sequence(
                        [
                            'type' => 'App\Notifications\ChallengeNotification',
                            'data' => "{'title':'You have received a challenge invitation from Seyijay',
                'action_type':'CHALLENGE','action_id':1}",
                        ],
                        [
                            'type' => 'App\Notifications\ChallengeNotification',
                            'data' => "{'title':'You have received a challenge invitation from Segun',
                'action_type':'CHALLENGE','action_id':1}",
                        ],
                        [
                            'type' => 'App\Notifications\ReferralBonusNotification',
                            'data' => "{'title':'You have received a referral bonus',
                'action_type':'BONUS','action_id':1}",
                        ],
                    )
                )->count(3),
                'notifications'
            )
            ->create();

        $this->actingAs($this->user);
    }

    public function test_can_get_user_notifications_if_no_brand_id_is_set()
    {

        $response = $this->getJson(self::NOTIFICATION_URL);

        $response->assertJsonCount(3, 'data.data');
    }

    public function test_can_get_user_notifications_if_a_brand_id_is_set()
    {

        $response = $this->withHeaders([
            'x-brand-id' => 3,
        ])->getJson("/api/v3/notifications");

        $response->assertJsonCount(3, 'data.data');
    }

    public function test_can_mark_single_notification_as_read()
    {
        $notification = UserNotification::first();
        $this->assertNull($notification->read_at);

        $response = $this->postJson(self::NOTIFICATION_URL . "/read/" . $notification->id);
        $response->assertOk();
        $notification = $notification->refresh();

        $this->assertNotNull($notification->read_at);
    }

    public function test_can_mark_all_notifications_as_read()
    {

        $this->postJson(self::NOTIFICATION_URL . "/read/all");

        $readCount = UserNotification::whereNotNull('read_at')->count();

        $this->assertEquals($readCount, 3);
    }

    // public function test_can_mark_all_notifications_as_read_does_not_read_challenge()
    // {

    //     $this->withHeaders([
    //         'x-brand-id' => 2,
    //     ])->postJson(self::NOTIFICATION_URL . "/read/all");

    //     $readCount = UserNotification::whereNotNull('read_at')->count();

    //     $this->assertEquals($readCount, 1);
    // }

    // public function test_that_challenge_notifications_are_not_returned_for_staking_platform()
    // {
    //     $response = $this->withHeaders([
    //         'x-brand-id' => 2,
    //     ])->getJson(self::NOTIFICATION_URL);

    //     $response->assertJsonCount(1, 'data.data');
    // }
}
