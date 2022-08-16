<?php

namespace Tests\Feature;

use App\Mail\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\DatabaseSeeder;
use UserSeeder;
use BoostSeeder;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

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

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
        $this->seed(BoostSeeder::class);
        $this->user = User::first();
        Mail::fake();
    }


    /** @test */
    public function a_user_cannot_register_with_empty_fields()
    {

        $response = $this->postjson(self::REGISTER_URL, [
            'first_name' => '',
            'last_name' => '',
            'username' => '',
            'phone_number' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => ''

        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function phone_number_cannot_be_less_than_11_digits()
    {

        $response = $this->postjson(self::REGISTER_URL, [
            'first_name' => 'User',
            'last_name' => 'Test',
            'username' => 'user',
            'phone_number' => '8883888383',
            'email' => 'user@user.com',
            'password' => 'password',
            'password_confirmation' => 'password'

        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function email_field_must_accept_a_valid_email_format()
    {

        $response = $this->postjson(self::REGISTER_URL, [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'username',
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

    public function test_a_user_recieves_verification_email_on_registration()
    {

        $response = $this->postjson(self::REGISTER_URL, [
            'first_name' => 'User',
            'last_name' => 'Test',
            'username' => 'user',
            'phone_number' => '88838883838',
            'email' => 'user@user.com',
            'password' => 'password',
            'password_confirmation' => 'password'

        ]);

        Mail::assertSent(VerifyEmail::class);
        $response->assertJson([
            'message' => 'Verification Email Sent',
        ]);
    }

    public function test_a_user_can_register_from_web_without_needing_email_verification()
    {
        $response = $this->withHeaders([
            'X-App-Source' => 'web',
        ])->postjson(self::REGISTER_URL, [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'username' => 'janeDoe',
            'phone_number' => '08012345678',
            'email' => 'jane@doe.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);


        $response->assertJson([
            'message' => 'Token',
        ]);
    }
}
