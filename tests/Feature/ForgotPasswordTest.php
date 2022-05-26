<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Mail\TokenGenerated;
use Illuminate\Support\Carbon;
use UserSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

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

    protected function setUp(): void{
        parent::setUp();
        $this->seed(UserSeeder::class);
        $this->user = User::first();   
        Mail::fake();
    }

    public function test_reset_email_can_be_sent()
    {   
        $response = $this->postjson(self::RESET_EMAIL_URL,[
            "email" => $this->user->email,
        ]);

        Mail::assertSent(TokenGenerated::class);
        $response->assertStatus(200);
    }

    public function test_email_must_be_registered()
    {
        $response = $this->postjson(self::RESET_EMAIL_URL,[
            "email" => "example@example.com",
        ]);

        $response->assertStatus(400);
    }

    public function test_that_reset_token_can_be_verified(){
        $now = Carbon::now();
        $token = mt_rand(10000,99999);

        DB::table('password_resets')->insert(['token' => $token, 
        'email' => $this->user->email, 'created_at' => $now]);

        $response = $this->postjson(self::VERIFY_TOKEN_URL,[
            "token" => strval($token),
        ]);

        $response->assertStatus(200);

    }

    public function test_that_reset_token_must_be_of_type_string(){
       
        $response = $this->postjson(self::VERIFY_TOKEN_URL,[
            "token" => 3466,
        ]);

        $response->assertJson([
            'message' => 'The token must be a string.',
        ]);

    }

    public function test_that_reset_token_must_exist_to_be_verified(){
    
        $response = $this->postjson(self::VERIFY_TOKEN_URL,[
            "token" => "9850",
        ]);

        $response->assertJson([
            'message' => 'Invalid verification code',
        ]);
    }

}
