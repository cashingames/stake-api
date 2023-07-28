<?php

namespace Tests\Unit;

use App\Models\User;
use App\Notifications\ChallengeStakingRefund;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;

class ChallengeRefundNotificationTest extends TestCase
{
    public function test_that_fcm_notification_channel_sends_notification()
    {
        Notification::fake();
 
        $user = User::factory()->create();

        $user->notify(new ChallengeStakingRefund(200));

        Notification::assertSentTo($user, ChallengeStakingRefund::class);
    }
}
