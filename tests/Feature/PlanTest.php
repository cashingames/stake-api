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
    }

    /** @test */
    public function a_user_can_subscribe_to_bronze_plan(){
        
        $wallet = $this->user->wallet()->create([
            'balance' => 200,
        ]);

        $response = $this->actingAs($this->user)->postJson(self::SUBSCRIBE_URL,[
            "plan_id" => 1,
        ]);
        
        $response->assertStatus(200);

    }
    /** @test */
    public function a_user_can_subscribe_to_silver_plan(){

        $wallet = $this->user->wallet()->create([
            'balance' => 300,
        ]);

        $response = $this->actingAs($this->user)->postJson(self::SUBSCRIBE_URL,[
            "plan_id" => 2,
        ]);
        
        $response->assertStatus(200);

    }

    /** @test */
    public function a_user_can_subscribe_to_gold_plan(){

        $wallet = $this->user->wallet()->create([
            'balance' => 500,
        ]);

        $response = $this->actingAs($this->user)->postJson(self::SUBSCRIBE_URL,[
            "plan_id" => 3,
        ]);
        
        $response->assertStatus(200);

    }
}
