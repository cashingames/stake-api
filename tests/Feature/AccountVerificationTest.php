<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AccountVerificationTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_should_not_verify_user_with_invalid_otp_or_phone_number(){
        $user = User::factory()->create([
            'phone_number' => '08012345678'
        ]);
        $response = $this->postJson("/api/auth/register/verify-token", [
            'phone_number' => $user->phone_number,
            'token' => rand(1000, 990000)
        ]);
        $response->assertJson([
            'message' => 'Invalid verification code'
        ]);
    }

    public function test_user_can_be_verified_with_valid_otp(){
        $user = User::factory()->create([
            'phone_number' => '8012345678'
        ]);
        $response = $this->postJson("/api/auth/register/verify-token", [
            'phone_number' => $user->phone_number,
            'token' => $user->otp_token
        ]);
        $response->assertJson([
            'message' => 'Verification successful'
        ]);
    }
}
