<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use UserSeeder;
use PlanSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;


class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    protected $user, $token, $now;
    const VALIDATE_URL = '/api/auth/token/verify';
    const RESET_PASSWORD_URL = '/api/auth/password/reset';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
        $this->seed(PlanSeeder::class);
        $this->user = User::first();
        $this->now = Carbon::now();
        $this->actingAs($this->user);
        $this->token = mt_rand(10000, 99999);
    }

    public function test_a_token_can_be_validated()
    {

        DB::insert('insert into password_resets (email, token, created_at) values (?, ?, ?)', [$this->user->email, $this->token, $this->now]);

        $response = $this->postjson(self::VALIDATE_URL, [
            "token" => strval($this->token),
        ]);

        $response->assertStatus(200);
    }

    /**
     * @todo this rest is faulty
     * @return void
     */
    public function test_a_user_can_reset_password()
    {

        DB::table('password_resets')->insert(['email' => $this->user->email, 'token' => 8989]);

        $resetDetails = DB::table('password_resets')->where('email', $this->user->email)->where('token', 8989)->first();

        $response = $this->postjson(self::RESET_PASSWORD_URL, [
            "email" => $this->user->email,
            "code" => "8989",
            "password" => "password111",
            "password_confirmation" => "password111"
        ]);

        $response->assertStatus(200);
    }
}
