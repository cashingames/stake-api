<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateGuestPlayerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
   
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        // $this->seed(UserSeeder::class);
        $this->user = User::create([
            'username' => 'guest1234',
            'email' => 'guest_gagge@gameark.com',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'otp_token'=> '',
            'is_on_line' => true,
            'password' => '12345678', // password
            'remember_token' => '',
            'is_a_bot' => false,
            'brand_id' => 1,
            'user_type' => 'GUEST_PLAYER'
        ]);

        $this->actingAs($this->user);
    }

    public function test_that_guest_user_profile_gets_updated()
    {
        $response = $this->post('/api/v3/guest/profile/update', [
            'username' => 'JayDee',
            'email' => 'johndoe@email.com',
            'password' => '12345678',
            'new_password' => 'password111',
            'new_password_confirmation' => 'password111',
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'username' => 'JayDee',
           'user_type' => 'PERMANENT_PLAYER'
        ]);
    }
}
