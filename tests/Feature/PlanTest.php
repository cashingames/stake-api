<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Database\Seeders\PlanSeeder;
use Database\Seeders\UserSeeder;
use App\Models\User;

class PlanTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    
    const SUBSCRIBE_URL = '/api/v1/plans/me/subscribe';
    protected $user;

    protected function setUp(): void{
        parent::setUp();
        
        $this->seed(UserSeeder::class);
        $this->seed(PlanSeeder::class);
        
        $this->user = User::first();   
        $this->actingAs($this->user);
    }

    /** @test */
    public function a_user_can_subscribe_to_bronze_plan_if_balance_is_150(){
        
        $this->user->wallet()->update([
            'balance' => 150,
        ]);

        $response = $this->postJson(self::SUBSCRIBE_URL,[
            "plan_id" => 1,
        ]); 
        $response->assertStatus(200);

    }

    /** @test */
    public function a_user_can_subscribe_to_bronze_plan_if_balance_is_more_than_150(){
    
        $this->user->wallet()->update([
            'balance' => 250,
        ]);

        $response = $this->postJson(self::SUBSCRIBE_URL,[
            "plan_id" => 1,
        ]); 
        $response->assertStatus(200);

    }

    /** @test */
    public function a_user_cannot_subscribe_to_bronze_plan_if_balance_is_less_than_150(){
    
        $this->user->wallet()->update([
            'balance' => 100,
        ]);

        $response = $this->postJson(self::SUBSCRIBE_URL,[
            "plan_id" => 1,
        ]); 
        $response->assertStatus(400);

    }

    /** @test */
    public function a_user_can_subscribe_to_silver_plan_if_balance_is_250(){

        $this->user->wallet()->update([
            'balance' => 250,
        ]);

        $response = $this->postJson(self::SUBSCRIBE_URL,[
            "plan_id" => 2,
        ]);
        
        $response->assertStatus(200);

    }

    /** @test */
    public function a_user_can_subscribe_to_silver_plan_if_balance_is_more_than_250(){

        $this->user->wallet()->update([
            'balance' => 300,
        ]);

        $response = $this->postJson(self::SUBSCRIBE_URL,[
            "plan_id" => 2,
        ]); 
        $response->assertStatus(200);

    }
    /** @test */
    public function a_user_cannot_subscribe_to_silver_plan_if_balance_is_less_than_250(){
    
        $this->user->wallet()->update([
            'balance' => 200,
        ]);

        $response = $this->postJson(self::SUBSCRIBE_URL,[
            "plan_id" => 2,
        ]); 
        $response->assertStatus(400);

    }

    /** @test */
    public function a_user_can_subscribe_to_gold_plan_if_balance_is_500(){

        $this->user->wallet()->update([
            'balance' => 500,
        ]);

        $response = $this->postJson(self::SUBSCRIBE_URL,[
            "plan_id" => 3,
        ]);
        
        $response->assertStatus(200);

    }

    /** @test */
    public function a_user_cannot_subscribe_to_gold_plan_if_balance_is_less_than_500(){

        $this->user->wallet()->update([
            'balance' => 400,
        ]);

        $response = $this->postJson(self::SUBSCRIBE_URL,[
            "plan_id" => 3,
        ]); 
        $response->assertStatus(400);

    }
    
    /** @test */
    public function a_user_can_subscribe_to_gold_plan_if_balance_is_more_than_500(){
    
        $this->user->wallet()->update([
            'balance' => 1000,
        ]);

        $response = $this->postJson(self::SUBSCRIBE_URL,[
            "plan_id" => 3,
        ]); 
        $response->assertStatus(200);

    }
}
