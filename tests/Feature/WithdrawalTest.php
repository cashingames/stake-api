<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WalletTransaction;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class WithdrawalTest extends TestCase
{   
    use RefreshDatabase;

    protected $user;
    const WITHDRAWAL_URL = '/api/v3/winnings/withdraw';
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->user = User::first();

        $this->actingAs($this->user);
        
    }

    public function test_that_a_user_cannot_withdraw_zero_naira(){
        $response = $this->post(self::WITHDRAWAL_URL);
        $response->assertJson([
            'message' => 'Invalid withdrawal amount. You can not withdraw NGN0',
        ]);
    }

    public function test_that_a_user_cannot_withdraw_more_than_maximum_withdrawal_limit_in_configurable_number_of_days(){

        Config::set('trivia.staking.total_withdrawal_limit', 10000);
        Config::set('trivia.staking.total_withdrawal_days_limit', 7);
        
        WalletTransaction::create([
            'wallet_id' => $this->user->wallet->id,
            'transaction_type' => 'DEBIT',
            'amount' => 15000,
            'balance' => $this->user->wallet->withdrawable_balance,
            'description' => 'Winnings Withdrawal Made',
            'reference' => Str::random(10),
            'created_at' => now()->subDays(3)
        ]);

        $this->user->wallet->withdrawable_balance = 20000;
        $this->user->wallet->save();
        
        $response = $this->post(self::WITHDRAWAL_URL);
        $response->assertJson([
            'message' => 'you cannot withdaw more than NGN' . config('trivia.staking.total_withdrawal_limit') . ' in ' . config('trivia.staking.total_withdrawal_days_limit') . ' days',
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

}
