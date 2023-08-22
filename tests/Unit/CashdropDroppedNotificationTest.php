<?php

namespace Tests\Unit;

use App\Models\User;
use App\Notifications\CashdropDroppedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;

class CashdropDroppedNotificationTest extends TestCase
{   
    use RefreshDatabase;
    public function test_that_cashdrop_dropped_notification_sends()
    {
        Notification::fake();
 
        $user = User::factory()->create();

        $user->notify(new CashdropDroppedNotification("johnDoe","Gold",2000));

        Notification::assertSentTo($user, CashdropDroppedNotification::class);
    }
}
