<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\Firebase\CloudMessagingService;
use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    public $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->user = User::first();

        $this->actingAs($this->user);
    }
    
    public function test_can_register_device_token(){
        $device_token = Str::uuid();
        $response = $this->postjson('/api/v3/fcm/subscriptions', [
            'device_token' => $device_token
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('fcm_push_subscriptions', [
            'device_token' => $device_token,
            'user_id' => $this->user->id
        ]);
    }

    // public function test_push_notification_gets_sent(){
    //     $spy = $this->spy(Client::class);

    //     $service = new CloudMessagingService("AAAAeT24s6U:APA91bH0-qhTOXle2_63EswIf-zQQ5wI139AwEkjrwt-fYzw8T2ENApizI2AMToDOIsh-xAAvVzD6ydhYwrn5aqp4Z7J91fmJas3ydBf0GPODNNqXKeIx6bSGor-hUE1tkT_oREeuMtE");
    //     $service->setTo("recipient");
    //     $service->setNotification([
    //         'title' => 'App notification',
    //         'body' => 'body'
    //     ])->setData([
    //         'title' => 'App notification',
    //         'body' => 'body content'
    //     ])->send();
        
    //     $spy->shouldHaveReceived('request');
    // }
}
