<?php

namespace Tests\Feature;

use App\Mail\SendEmailOTP;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Database\Seeders\UserSeeder;

class StakersEmailVerificationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    protected $user;
    const VERIFY_TOKEN_URL = '/api/v3/stakers/email/verify';
    const SEND_TOKENL_URL  = '/api/v3/stakers/otp/send';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
        $this->user = User::first();
        $this->actingAs($this->user);

        Mail::fake();
    }

    public function test_stakers_email_verification_otp_can_be_sent()
    {
        $this->withHeaders([
            'x-brand-id' => 2
        ])->post(self::SEND_TOKENL_URL);

        Mail::assertSent(SendEmailOTP::class);
        $this->assertTrue(!is_null($this->user->otp_token));
    }

    public function test_stakers_email_verification_can_be_verified()
    {   
        $this->user->update(['email_verified_at' => null]);

        $this->withHeaders([
            'x-brand-id' => 2
        ])->postJson(self::VERIFY_TOKEN_URL,[
            'token' => $this->user->otp_token
        ]);

        $this->assertTrue(!is_null($this->user->email_verified_at));
    }

    public function test_stakers_email_verification_cannot_be_verified_with_wrong_otp()
    {   
        $this->user->update(['email_verified_at' => null]);

        $this->withHeaders([
            'x-brand-id' => 2
        ])->postJson(self::VERIFY_TOKEN_URL,[
            'token' =>  mt_rand(10000, 99999)
        ]);

        $this->assertTrue(is_null($this->user->email_verified_at));
    }
}
