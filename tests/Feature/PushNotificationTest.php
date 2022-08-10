<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\WithFaker;
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
}
