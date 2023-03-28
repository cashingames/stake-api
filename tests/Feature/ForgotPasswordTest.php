<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Mail\TokenGenerated;
use App\Services\SMS\SMSProviderInterface;
use Illuminate\Support\Carbon;
use UserSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    protected $user;
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
    }

    public function test_reset_email_can_be_sent()
    {
        $response = $this->postjson(self::RESET_EMAIL_URL, [
            "email" => $this->user->email,
        ]);

        Mail::assertQueued(TokenGenerated::class);
        $response->assertStatus(200);
    }

    public function test_reset_email_can_be_sent_from_GameArk()
    {
        $response = $this->withHeaders(['x-brand-id' => 10])->postjson(self::RESET_EMAIL_URL, [
            "email" => $this->user->email,
        ]);

        Mail::assertQueued(TokenGenerated::class);
        $response->assertStatus(200);
    }

    // public function test_email_must_be_registered()
    // {
    //     $response = $this->postjson(self::RESET_EMAIL_URL,[
    //         "email" => "example@example.com",
    //     ]);

    //     $response->assertStatus(200);
    // }

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

        $this->user->update(['otp_token' => 9095]);
        $this->user->refresh();

        $response = $this->withHeaders(['x-brand-id' => 2])->postjson(self::VERIFY_TOKEN_URL, [
            "token" => "9095",
        ]);

        $response->assertJson([
            'message' => 'Verification successful'
        ]);
    }
}
