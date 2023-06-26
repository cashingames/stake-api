<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AuthToken;
use Mockery\MockInterface;
use App\Enums\AuthTokenType;
use App\Mail\TokenGenerated;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Services\SMS\SMSProviderInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    protected $user;
    protected $authTokenRecord;

    const RESET_EMAIL_URL = '/api/auth/password/email';
    const VERIFY_TOKEN_URL = '/api/auth/token/verify';

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()
            ->count(1)
            ->create();
        $this->user = User::first();
        config(['services.termii.api_key' => 'termii_api_key']);
        Mail::fake();
        config(['auth.verification.minutes_before_otp_expiry' => 5]);

        $this->authTokenRecord = AuthToken::create([
            'user_id' => $this->user->id,
            'token' => mt_rand(10000, 99999),
            'token_type' => AuthTokenType::PhoneVerification->value,
            'expire_at' => now()->addMinutes(config('auth.verification.minutes_before_otp_expiry'))->toDateTimeString()
        ]);
    }

    public function test_reset_email_can_be_sent()
    {
        $response = $this->postjson(self::RESET_EMAIL_URL, [
            "email" => $this->user->email,
        ]);

        Mail::assertSent(TokenGenerated::class);
        $response->assertStatus(200);
    }

    public function test_reset_email_can_be_sent_from_GameArk()
    {
        $response = $this->withHeaders(['x-brand-id' => 10])->postjson(self::RESET_EMAIL_URL, [
            "email" => $this->user->email,
        ]);

        Mail::assertSent(TokenGenerated::class);
        $response->assertStatus(200);
    }

    public function test_that_reset_token_can_be_verified()
    {
        $now = Carbon::now();
        $token = mt_rand(10000, 99999);

        DB::table('password_resets')->insert([
            'token' => $token,
            'email' => $this->user->email, 'created_at' => $now
        ]);

        $response = $this->postjson(self::VERIFY_TOKEN_URL, [
            "token" => strval($token),
        ]);

        $response->assertStatus(200);
    }

    public function test_that_reset_token_must_be_of_type_string()
    {

        $response = $this->postjson(self::VERIFY_TOKEN_URL, [
            "token" => 3466,
        ]);

        $response->assertJson([
            'message' => 'The token must be a string.',
        ]);
    }

    public function test_that_reset_token_must_exist_to_be_verified()
    {

        $response = $this->postjson(self::VERIFY_TOKEN_URL, [
            "token" => "9850",
        ]);

        $response->assertJson([
            'message' => 'Invalid verification code',
        ]);
    }

    public function test_a_user_recieves_sms_otp_on_forgot_password_reset_on_stakers_app()
    {
        $this->user->update(['phone_number' => 90958886969]);

        $this->mock(SMSProviderInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('deliverOTP')->once();
        });

        $response = $this->withHeaders(['x-brand-id' => 2])->postjson(self::RESET_EMAIL_URL, [
            'country_code' => '+234',
            'phone_number' =>  90958886969,
        ]);

        $response->assertJson([
            'message' => 'OTP Sent'
        ]);
    }

    public function test_that_reset_token_can_be_verified_for_stakers_app()
    {
        $response = $this->withHeaders(['x-brand-id' => 2])->postjson(self::VERIFY_TOKEN_URL, [
            "token" => strval($this->authTokenRecord->token),
        ]);

        $response->assertJson([
            'message' => 'Verification successful'
        ]);
    }
}
