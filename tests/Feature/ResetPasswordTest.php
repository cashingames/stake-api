<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AuthToken;
use Mockery\MockInterface;
use App\Enums\AuthTokenType;
use Illuminate\Support\Facades\Mail;
use App\Services\SMS\SMSProviderInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @TODO Test reset password
 */
class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    protected $user;
    protected $authTokenRecord;

    const URL = '/api/auth/password/reset';
   

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()
            ->count(1)
            ->create();
        $this->user = User::first();

        $this->authTokenRecord = AuthToken::create([
            'user_id' => $this->user->id,
            'token' => mt_rand(10000, 99999),
            'token_type' => AuthTokenType::PhoneVerification->value,
            'expire_at' => now()->addMinutes(config('auth.verification.minutes_before_otp_expiry'))->toDateTimeString()
        ]);
    }

    public function test_that_existing_user_can_reset_password()
    {   
        $this->user->update(['phone_number' => "1234556778"]);
        
        $response = $this->postjson(self::URL, [
            "phone" => $this->user->phone_number,
            "password" => "passwordNew1234",
            "password_confirmation" => "passwordNew1234",
            "code" =>  strval($this->authTokenRecord->token)
        ]);

        $response->assertJson([
            'message' => 'Password reset successful',
        ]);
    }

    public function test_that_non_existing_user_cannot_reset_password()
    {   

        $response = $this->postjson(self::URL, [
            "phone" => '8888888999',
            "password" => "passwordNew1234",
            "password_confirmation" => "passwordNew1234",
            "code" =>  '12345'
        ]);

        $response->assertJson([
            'message' => 'Phone number does not exist',
        ]);
    }


  
}