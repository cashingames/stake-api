<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use UserSeeder;
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
    const RESET_PASSWORD_URL = '/api/auth/password/reset/';

    protected function setUp(): void{
        parent::setUp();
        $this->seed(UserSeeder::class);
        $this->user = User::first();  
        $this->now = Carbon::now();
        $this->token = mt_rand(10000,99999);
 
    }

    public function test_a_token_can_be_validated(){

        DB::insert('insert into password_resets (email, token, created_at) values (?, ?, ?)', [ $this->user->email, $this->token, $this->now]);
        
        $response = $this->postjson(self::VALIDATE_URL,[
            "token" =>strval($this->token),
        ]);
       
        $response->assertStatus(200);
       
    }

    public function test_a_user_can_reset_password(){

        $response = $this->postjson(self::RESET_PASSWORD_URL.$this->user->email, [
            "password" => "password111",
            "password_confirmation" => "password111"
        ]);

        $response->assertStatus(200);
    }

   
}
