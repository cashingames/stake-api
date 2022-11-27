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
use App\Services\Payments\PaystackWithdrawalService;
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

    // public function test_that_a_user_cannot_withdraw_more_than_maximum_withdrawal_limit_in_configurable_number_of_days(){

    //     Config::set('trivia.staking.total_withdrawal_limit', 10000);
    //     Config::set('trivia.staking.total_withdrawal_days_limit', 7);
        
    //     WalletTransaction::create([
    //         'wallet_id' => $this->user->wallet->id,
    //         'transaction_type' => 'DEBIT',
    //         'amount' => 15000,
    //         'balance' => $this->user->wallet->withdrawable_balance,
    //         'description' => 'Winnings Withdrawal Made',
    //         'reference' => Str::random(10),
    //         'created_at' => now()->subDays(3)
    //     ]);

    //     $this->user->wallet->withdrawable_balance = 20000;
    //     $this->user->wallet->save();
        
    //     $response = $this->post(self::WITHDRAWAL_URL);
    //     $response->assertJson([
    //         'message' => 'you cannot withdaw more than NGN' . config('trivia.staking.total_withdrawal_limit') . ' in ' . config('trivia.staking.total_withdrawal_days_limit') . ' days',
    //     ]);
    // }

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
        $this->mock(PaystackWithdrawalService::class, function (MockInterface $mock) use($banksMock){
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
        $this->mock(PaystackWithdrawalService::class, function (MockInterface $mock) use ($banksMock) {
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
        
        $this->mock(PaystackWithdrawalService::class, function (MockInterface $mock){
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
        $this->mock(PaystackWithdrawalService::class, function (MockInterface $mock) {
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

}
