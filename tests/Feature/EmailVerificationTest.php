<?php

namespace Tests\Feature;

use App\Enums\AuthTokenType;
use App\Mail\SendEmailOTP;
use App\Models\AuthToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Database\Seeders\UserSeeder;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    protected $user , $authTokenRecord ;
    const VERIFY_TOKEN_URL = '/api/v3/stakers/email/verify';
    const SEND_TOKENL_URL  = '/api/v3/stakers/otp/send';

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()
            ->count(1)
            ->create();
        $this->user = User::first();
        $this->actingAs($this->user);
        $this->withHeaders([
            'x-brand-id' => 2
        ]);

        Mail::fake();
        config(['auth.verification.minutes_before_otp_expiry' => 5]);

        $this->authTokenRecord = AuthToken::create([
            'user_id' => $this->user->id,
            'token' => mt_rand(10000, 99999),
            'token_type' => AuthTokenType::EmailVerification->value,
            'expire_at' => now()->addMinutes(config('auth.verification.minutes_before_otp_expiry'))->toDateTimeString()
        ]);
    }

    public function test_stakers_email_verification_otp_can_be_sent()
    {
        $this->user->update(['email_verified_at' => null]);
       

        $this->post(self::SEND_TOKENL_URL);

        Mail::assertSent(SendEmailOTP::class);
        $this->assertTrue(!is_null($this->authTokenRecord->token));
    }

    public function test_stakers_email_can_be_verified()
    {
        $this->user->update(['email_verified_at' => null]);
        $this->postJson(self::VERIFY_TOKEN_URL, [
            'token' => $this->authTokenRecord->token
        ]);

        $this->user->refresh();
        $this->assertTrue(!is_null($this->user->email_verified_at));
    }

    public function test_stakers_email_verification_cannot_be_verified_with_wrong_otp()
    {
        $this->user->update(['email_verified_at' => null]);
        $this->postJson(self::VERIFY_TOKEN_URL, [
            'token' =>  mt_rand(10000, 99999)
        ]);

        $this->user->refresh();
        $this->assertTrue(is_null($this->user->email_verified_at));
    }
}
