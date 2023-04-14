<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use UserSeeder;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    /** @test */

    const AUTH_URL = '/api/auth/login';
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
        $this->user = User::first();
    }

    public function login_fields_cannot_be_empty()
    {

        $response = $this->postjson(self::AUTH_URL, [
            "email" => " ",
            "password" => " ",
        ]);


        $response->assertStatus(400);
        $response->assertJson([
            'errors' => 'Invalid email or password',
        ]);
    }

    /** @test */
    public function a_user_cannot_login_with_non_existent_email()
    {

        $response = $this->postjson(self::AUTH_URL, [
            "email" => "4995858595@email.com",
            "password" => "kkkfjffj",
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'errors' => 'Invalid email or password',
        ]);
    }

    public function test_a_user_can_login_with_email()
    {

        $response = $this->postjson(self::AUTH_URL, [
            "email" => $this->user->email,
            "password" => "password",
        ]);

        $response->assertStatus(200);
    }

    public function test_a_user_cannot_login_with_wrong_email_format()
    {
        $response = $this->postjson(self::AUTH_URL, [
            "email" => "email@email",
            "password" => "password",
        ]);

        $response->assertStatus(400);
    }

    public function test_a_user_can_login_with_username()
    {

        $response = $this->postjson(self::AUTH_URL, [
            "email" => $this->user->username,
            "password" => "password",
        ]);

        $response->assertStatus(200);
    }

    public function test_a_user_cannot_login_with_non_existent_username()
    {

        $response = $this->postjson(self::AUTH_URL, [
            "email" => "randomString",
            "password" => "password",
        ]);

        $response->assertJson([
            'errors' => 'Invalid email or password',
        ]);
    }

    public function test_a_user_cannot_login_with_wrong_password()
    {

        $response = $this->postjson(self::AUTH_URL, [
            "email" => $this->user->email,
            "password" => "wrongPassword",
        ]);

        $response->assertJson([
            'errors' => 'Invalid email or password',
        ]);
    }
}
