<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Mockery\MockInterface;
use Illuminate\Support\Str;
use Database\Seeders\UserSeeder;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Payments\PaystackService;
use Exception;

class WithdrawalTest extends TestCase
{   
    use RefreshDatabase;

    protected $user;

    protected $banksMock;

    const WITHDRAWAL_URL = '/api/v3/winnings/withdraw';
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->user = User::first();

        $this->banksMock = json_decode(json_encode([
            'data' => [
                [
                    'name' => 'Test Bank',
                    'code' => "059"
                ]
            ]
        ]));

        $this->actingAs($this->user);
        
    }

    public function test_that_a_user_cannot_withdraw_zero_naira(){
        $response = $this->post(self::WITHDRAWAL_URL);
        $response->assertJson([
            'message' => 'Invalid withdrawal amount. You can not withdraw NGN0',
        ]);
    }

    public function test_that_a_user_is_forced_to_verify_email_if_max_limit_is_exceeded(){

        Config::set('trivia.email_verification_limit_threshold', 10000);
        $this->user->update(['email_verified_at' => null]);
    
        $this->user->wallet->withdrawable_balance = 20000;
        $this->user->wallet->save();
       
        WalletTransaction::create([
            'wallet_id' => $this->user->wallet->id,
            'transaction_type' => 'DEBIT',
            'amount' => 15000,
            'balance' => $this->user->wallet->withdrawable_balance,
            'description' => 'Winnings Withdrawal Made',
            'reference' => Str::random(10),
            'created_at' => now()->subHours(3)
        ]);
    
        $response = $this->withHeaders(['x-brand-id' => 2])->post(self::WITHDRAWAL_URL);
        
        $response->assertJsonFragment([
            'verifyEmailNavigation' => true,
        ]);
    }

    public function test_that_a_user_cannot_withdraw_less_than_configurable_one_time_minimum_withrawal_amount(){
        Config::set('trivia.staking.min_withdrawal_amount', 500);

        $this->user->wallet->withdrawable_balance = 150;
        $this->user->wallet->save();


        $response = $this->post(self::WITHDRAWAL_URL);
        $response->assertJson([
            'message' => 'You can not withdraw less than NGN' . config('trivia.staking.min_withdrawal_amount'),
        ]);
    }
   
    public function test_that_a_user_cannot_withdraw_without_updating_bank_details(){
        
        $this->user->profile->bank_name =  NULL;
        $this->user->profile->account_number = NULL;
        $this->user->profile->save();

        $response = $this->post(self::WITHDRAWAL_URL);
        $response->assertJson([
            'message' => 'Please update your profile with your bank details',
        ]);
    }

    public function test_that_user_can_withdraw_successfully(){
        $banksMock = $this->banksMock;
        $this->mock(PaystackService::class, function (MockInterface $mock) use($banksMock){
            $mock->shouldReceive('getBanks')->once()->andReturn(
                $banksMock
            );
            $mock->shouldReceive('verifyAccount')->andReturnTrue();
            $mock->shouldReceive('createTransferRecipient')->andReturn('randomrecipientcode');
            $mock->shouldReceive('initiateTransfer')->andReturn((object)[
                'status' => 'success',
                'reference' => 'randomref'
            ]);
        });
        $this->user->wallet->withdrawable_balance = 5000;
        $this->user->wallet->save();

        $response = $this->post(self::WITHDRAWAL_URL);

        $response->assertJson([
            'message' => 'Your transfer is being successfully processed to your bank account'
        ]);
    }

    public function test_pending_response_from_payment_gateway(){
        $banksMock = $this->banksMock;
        $this->mock(PaystackService::class, function (MockInterface $mock) use ($banksMock) {
            $mock->shouldReceive('getBanks')->once()->andReturn(
                $banksMock
            );
            $mock->shouldReceive('verifyAccount')->andReturnTrue();
            $mock->shouldReceive('createTransferRecipient')->andReturn('randomrecipientcode');
            $mock->shouldReceive('initiateTransfer')->andReturn((object)[
                'status' => 'pending',
                'reference' => 'randomref'
            ]);
        });
        $this->user->wallet->withdrawable_balance = 5000;
        $this->user->wallet->save();

        $response = $this->post(self::WITHDRAWAL_URL);

        $response->assertJson([
            'message' => 'Transfer processing, wait for your bank account to reflect'
        ]);
    }

    public function test_that_money_is_not_send_to_unverified_bank_account(){
        
        $this->mock(PaystackService::class, function (MockInterface $mock){
            $mock->shouldReceive('getBanks')->once()->andReturn(
                $this->banksMock
            );
            $mock->shouldReceive('verifyAccount')->andReturnFalse();
        });

        $this->user->wallet->withdrawable_balance = 5000;
        $this->user->wallet->save();

        $response = $this->post(self::WITHDRAWAL_URL);

        $response->assertJson([
            'message' => 'Account is not valid'
        ]);
    }

    public function test_error_handled_when_withdrawal_initiation_goes_wrong_at_provider(){
        $this->mock(PaystackService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getBanks')->once()->andReturn(
                $this->banksMock
            );
            $mock->shouldReceive('verifyAccount')->andReturnTrue();
            $mock->shouldReceive('createTransferRecipient')->andReturn('randomrecipientcode');
            $mock->shouldReceive('initiateTransfer')->andThrowExceptions([new Exception()]);
        });

        $this->user->wallet->withdrawable_balance = 5000;
        $this->user->wallet->save();

        $response = $this->post(self::WITHDRAWAL_URL);

        $response->assertJson([
            'message' => 'We are unable to complete your withdrawal request at this time, please try in a short while or contact support'
        ]);
    }


    public function test_that_a_deleted_account_cannot_make_withdrawal() {
        $this->user->delete();
        $response = $this->post(self::WITHDRAWAL_URL);

        $response->assertStatus(500);
    }

}
