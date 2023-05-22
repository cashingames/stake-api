<?php

namespace Tests\Feature;

use App\Enums\AuthTokenType;
use App\Models\AuthToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AccountVerificationTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_should_not_verify_user_with_invalid_otp_or_phone_number(){
        $user = User::factory()->create([
            'phone_number' => '8012345678'
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

        config(['auth.verification.minutes_before_otp_expiry' => 5]);

        $authTokenRecord = AuthToken::create([
            'user_id' => $user->id,
            'token' => mt_rand(10000, 99999),
            'token_type' => AuthTokenType::PhoneVerification->value,
            'expire_at' => now()->addMinutes(config('auth.verification.minutes_before_otp_expiry'))->toDateTimeString()
        ]);
        $response = $this->postJson("/api/auth/register/verify-token", [
            'phone_number' => $user->phone_number,
            'token' => $authTokenRecord->token
        ]);
        $response->assertJson([
            'message' => 'Verification successful'
        ]);
    }

    public function test_user_cannot_be_verified_with_expired_otp(){
        $user = User::factory()->create([
            'phone_number' => '8012345678'
        ]);

        config(['auth.verification.minutes_before_otp_expiry' => 5]);

        $authTokenRecord = AuthToken::create([
            'user_id' => $user->id,
            'token' => mt_rand(10000, 99999),
            'token_type' => AuthTokenType::PhoneVerification->value,
            'expire_at' => now()->subMinutes(config('auth.verification.minutes_before_otp_expiry'))->toDateTimeString()
        ]);
        $response = $this->postJson("/api/auth/register/verify-token", [
            'phone_number' => $user->phone_number,
            'token' => $authTokenRecord->token
        ]);
        $response->assertJson([
            'message' => 'Invalid verification code'
        ]);
    }
}
