<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Database\Seeders\UserSeeder;
use App\Models\User;

class WalletTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    protected $user;

    protected function setUp(): void{
        parent::setUp();
        
        $this->seed(UserSeeder::class);
        $this->user = User::first(); 

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

    public function test_a_transaction_cannot_be_verified_with_invalid_reference_id(){

        $reference = uniqid();
        Http::fake([
            'https://api.paystack.co/transaction/verify/'.$reference =>Http::response([
                "status"=> false,
            ])
        ]);

        $response = $this->get('/api/v1/wallet/me/transaction/verify/'.$reference);
        $response->assertJson([
            'message' => 'Payment could not be verified. Please wait for your balance to reflect.',
        ]);
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


}
