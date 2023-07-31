<?php

namespace Tests\Feature;

use App\Enums\FeatureFlags;
use BoostSeeder;
use Tests\TestCase;
use App\Models\User;
use App\Mail\VerifyEmail;
use App\Mail\WelcomeEmail;
use App\Models\Boost;
use App\Services\FeatureFlag;
use Mockery\MockInterface;
use Illuminate\Support\Facades\Mail;
use App\Services\SMS\SMSProviderInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // public function testExample()
    // {
    //     $response = $this->get('/');

    //     $response->assertStatus(200);
    // }

    protected $user;
    const REGISTER_URL = '/api/auth/register';
    const RESEND_OTP_URL = '/api/auth/register/token/resend';
    const SOCIAL_REGISTRATION_URL = '/api/auth/social-login/authenticate';

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()
            ->count(1)
            ->create();
        $this->seed(BoostSeeder::class);
        $this->user = User::first();
        Mail::fake();
        config(['services.termii.api_key' => 'termii_api_key']);
    }


    /** @test */
    public function a_user_cannot_register_with_empty_fields()
    {

        $response = $this->postjson(self::REGISTER_URL, [
            'first_name' => '',
            'last_name' => '',
            'username' => '',
            'country_code' => '',
            'phone_number' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => ''

        ]);

        $response->assertStatus(422);
    }

    public function email_field_must_accept_a_valid_email_format()
    {

        $response = $this->postjson(self::REGISTER_URL, [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'username',
            'country_code' => '234',
            'phone_number' => '12345678909',
            'email' => 'user.com',
            'password' => 'password',
            'password_confirmation' => 'password'

        ]);

        $response->assertStatus(422);
    }
    /** @test */
    public function firstname_and_lastname_cannot_be_numbers()
    {

        $response = $this->postjson(self::REGISTER_URL, [
            'first_name' => 102000300,
            'last_name' => 1009399494,
            'username' => 'user1',
            'phone_number' => '88838383844',
            'email' => 'email@user.com',
            'password' => 'password',
            'password_confirmation' => 'password'

        ]);
        $response->assertStatus(422);
    }

    /** @test */
    public function username_cannot_contain_special_characters()
    {

        $response = $this->postjson(self::REGISTER_URL, [
            'first_name' => "New",
            'last_name' => "User",
            'username' => 'user@$*1',
            'phone_number' => '88838383844',
            'email' => 'email@user.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'country_code' => '+234'

        ]);
        $response->assertJsonFragment([
            'message' => 'The username must only contain letters and numbers.',
        ]);
    }

    /** @test */
    public function a_user_cannot_register_with_existing_username_email_or_phone_number()
    {

        $response = $this->postjson(self::REGISTER_URL, [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'username' => $this->user->username,
            'phone_number' => $this->user->phone_number,
            'email' => $this->user->email,
            'password' => 'password',
            'password_confirmation' => 'password'

        ]);

        $response->assertStatus(422);
    }


    public function test_new_user_gets_boost_bonus()
    {
        $this->postjson(self::REGISTER_URL, [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'username' => 'janeDoe',
            'country_code' => '+234',
            'phone_number' => '7098498884',
            'email' => 'email@email.com',
            'password' => 'password',
            'password_confirmation' => 'password'

        ]);

        $user = User::where('email', 'email@email.com' )->first();

        $this->assertDatabaseHas('user_boosts', [
            'user_id' => $user->id,
            'boost_id' => Boost::where('name', 'Time Freeze')->first()->id,
            'boost_count' => 3,
            'used_count' => 0
        ]);

        $this->assertDatabaseHas('user_boosts', [
            'user_id' => $user->id,
            'boost_id' => Boost::where('name', 'Skip')->first()->id,
            'boost_count' => 3,
            'used_count' => 0
        ]);
    }

    /** @test */
    public function user_can_sign_in_with_social_auth_directly()
    {

        $response = $this->withHeaders([
            'x-brand-id' => 10,
        ])->postjson(self::SOCIAL_REGISTRATION_URL, [
            'firstName' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'email@email.com'
        ]);

        Mail::assertSent(WelcomeEmail::class);
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_register_without_otp()
    {

        $response = $this->withHeaders(['x-brand-id' => 10])->postjson(self::REGISTER_URL, [
            'username' => 'username',
            'email' => 'tester@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password'

        ]);

        // $response->assertStatus(200);

        Mail::assertSent(WelcomeEmail::class);
        $response->assertJsonStructure([
            "message",
            "data" => [
                "token"
            ]
        ]);
    }

    public function test_that_a_guest_user_can_be_created_with_autogenerated_username()
    {
        $response = $this->withHeaders(['x-brand-id' => 10])->postjson(self::REGISTER_URL, [
            'email' => 'tester@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'guest'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'user_type' => 'GUEST_PLAYER'
        ]);
    }

    public function test_that_a_guest_user_can_be_created_with_autogenerated_email()
    {
        $response = $this->withHeaders(['x-brand-id' => 10])->postjson(self::REGISTER_URL, [
            // 'email' => 'tester@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'guest'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'user_type' => 'GUEST_PLAYER'
        ]);
    }
}
