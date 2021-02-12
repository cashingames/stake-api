<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Database\Seeders\UserSeeder;
use Database\Seeders\VoucherSeeder;
use App\Models\User;
use App\Models\Voucher;

class WalletTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    const VOUCHER_URL = '/api/v1/voucher/consume/';
    protected $user;
    protected $voucher;

    protected function setUp(): void{
        parent::setUp();
        
        $this->seed(UserSeeder::class);
        $this->seed(VoucherSeeder::class);
        
        $this->user = User::first(); 
        $this->voucher = Voucher::first();

        $this->actingAs($this->user);
    }
    
    public function test_a_transaction_can_be_verified(){

        $reference = uniqid();
        Http::fake([
            'https://api.paystack.co/transaction/verify/'.$reference =>Http::response([
                "status"=> true,
                "message"=> "Verification successful",
                "data"=> [
                  "reference"=> "nms6uvr1pl",
                  "amount"=> 20000,
                ],
            ], 200)
        ]);

        $response = $this->get('/api/v1/wallet/me/transaction/verify/'.$reference);
        $response->assertStatus(200);
    }

    public function test_a_user_can_make_a_withdrawal_request(){
        $this->user->wallet()->update([
            'account2' => 2500,
        ]);

        $bankName= $this->user->profile->bank_name;
        $accountName = $this->user->profile->account_name;
        $accountNumber = $this->user->profile->account_number;
        $response = $this->post('/api/v1/wallet/me/withdrawal/'.$bankName.'/'.$accountName.'/'.$accountNumber.'/1000');
        
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Withrawal Request sent.',
        ]);
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
