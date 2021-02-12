<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\UserSeeder;
use Database\Seeders\VoucherSeeder;
use App\Models\User;
use App\Models\Voucher;

class VoucherTest extends TestCase
{   
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    const VOUCHER_URL = '/api/v1/voucher/consume/';
    protected $voucher;
    protected $user;

    protected function setUp(): void{
        parent::setUp();
        
        $this->seed(UserSeeder::class);
        $this->seed(VoucherSeeder::class);
        
        $this->user = User::first(); 
        $this->voucher = Voucher::first();

        $this->actingAs($this->user);
    }

    public function test_a_voucher_can_be_consumed(){
        
        $response = $this->post(self::VOUCHER_URL.$this->voucher->code);
        
        $response->assertStatus(200);
    }

    public function test_a_user_cannot_consume_invalid_voucher(){
        
        $response = $this->post(self::VOUCHER_URL.'920038DGGBA');
        
        $response->assertStatus(400);
    }

    public function test_a_user_cannot_consume_expired_voucher(){
        
        $this->voucher->update([
            'expire' => now(),
        ]);

        $response = $this->post(self::VOUCHER_URL.$this->voucher->code);
        
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Sorry, your voucher code has expired!',
        ]);
    }

    public function test_a_user_cannot_consume_voucher_with_exhausted_limit(){
        
        $this->voucher->update([
            'count' => 0,
        ]);

        $response = $this->post(self::VOUCHER_URL.$this->voucher->code);
        
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Sorry, your voucher limit has been exhausted!',
        ]);
        
    }
    
}
