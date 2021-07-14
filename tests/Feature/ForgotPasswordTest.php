<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Mail\TokenGenerated;
use UserSeeder;
use Illuminate\Support\Facades\Mail;

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

}
