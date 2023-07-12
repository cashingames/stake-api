<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Mockery\MockInterface;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Payments\PaystackService;
use Database\Seeders\BonusSeeder;
use Exception;

class WithdrawalTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $banksMock;

    protected $verificationMock;

    const WITHDRAWAL_URL = '/api/v3/winnings/withdraw';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->user = User::first();
        $this->seed(BonusSeeder::class);
        
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


    public function test_that_a_user_cannot_withdraw_less_than_configurable_one_time_minimum_withrawal_amount()
    {
        Config::set('trivia.staking.min_withdrawal_amount', 500);

        $this->user->wallet->withdrawable = 150;
        $this->user->wallet->save();

        $this->post(self::WITHDRAWAL_URL, [
            'account_number' => '124567890',
            'account_name' => 'Test User',
            'amount' => 150,
            'bank_name' => 'Test Bank'
        ])->assertSessionHasErrors(['amount' => 'The amount must be at least 500.']);
    }

    public function test_that_a_user_cannot_withdraw_if_name_is_not_equal_to_account_name()
    {
        $banksMock = $this->banksMock;
        $this->mock(PaystackService::class, function (MockInterface $mock) use ($banksMock) {
            $mock->shouldReceive('getBanks')->once()->andReturn(
                $banksMock
            );
            $mock->shouldReceive('verifyAccount')->andReturn((object)[
                'status' => true,
                'data' => (object)[
                    'account_name' => 'TEST ACCOUNT'
                ]
            ]);
            $mock->shouldReceive('createTransferRecipient')->andReturn('randomrecipientcode');
            $mock->shouldReceive('initiateTransfer')->andReturn((object)[
                'status' => 'success',
                'reference' => 'randomref'
            ]);
        });
        $this->user->wallet->withdrawable = 1000;
        $this->user->wallet->save();

        $response = $this->post(self::WITHDRAWAL_URL, [
            'account_number' => '124567890',
            'account_name' => 'Test User',
            'amount' => 500,
            'bank_name' => 'Test Bank'

        ]);
        $response->assertJson([
            'message' => 'Account name does not match your registration name. Please contact support.',
        ]);
    }

    public function test_that_user_can_withdraw_successfully()
    {
        $banksMock = $this->banksMock;
        $this->mock(PaystackService::class, function (MockInterface $mock) use ($banksMock) {
            $mock->shouldReceive('getBanks')->once()->andReturn(
                $banksMock
            );
            $mock->shouldReceive('verifyAccount')->andReturn((object)[
                'status' => true,
                'data' => (object)[
                    'account_name' => strtoupper($this->user->profile->first_name) . " " . strtoupper($this->user->profile->last_name)
                ]
            ]);

            $mock->shouldReceive('createTransferRecipient')->andReturn('randomrecipientcode');
            $mock->shouldReceive('initiateTransfer')->andReturn((object)[
                'status' => 'success',
                'reference' => 'randomref'
            ]);
        });
        $this->user->wallet->withdrawable = 5000;
        $this->user->wallet->save();

        $response = $this->post(self::WITHDRAWAL_URL, [
            'account_number' => '124567890',
            'account_name' => 'Test User',
            'amount' => 500,
            'bank_name' => 'Test Bank'

        ]);

        $response->assertJson([
            'message' => 'Your transfer is being successfully processed to your bank account'
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $this->user->wallet->id,
            'transaction_type' => "DEBIT",
            'description' => 'Successful Withdrawal',
            'balance' => $this->user->wallet->withdrawable
        ]);
    }

    public function test_pending_response_from_payment_gateway()
    {
        $banksMock = $this->banksMock;
        $this->mock(PaystackService::class, function (MockInterface $mock) use ($banksMock) {
            $mock->shouldReceive('getBanks')->once()->andReturn(
                $banksMock
            );
            $mock->shouldReceive('verifyAccount')->andReturn((object)[
                'status' => true,
                'data' => (object)[
                    'account_name' => strtoupper($this->user->profile->first_name) . " " . strtoupper($this->user->profile->last_name)
                ]
            ]);
            $mock->shouldReceive('createTransferRecipient')->andReturn('randomrecipientcode');
            $mock->shouldReceive('initiateTransfer')->andReturn((object)[
                'status' => 'pending',
                'reference' => 'randomref'
            ]);
        });
        $this->user->wallet->withdrawable = 5000;
        $this->user->wallet->save();

        $response = $this->post(self::WITHDRAWAL_URL, [
            'account_number' => '124567890',
            'account_name' => 'Test User',
            'amount' => 500,
            'bank_name' => 'Test Bank'

        ]);

        $response->assertJson([
            'message' => 'Transfer processing, wait for your bank account to reflect'
        ]);
    }

    public function test_that_money_is_not_send_to_unverified_bank_account()
    {

        $this->mock(PaystackService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getBanks')->once()->andReturn(
                $this->banksMock
            );
            $mock->shouldReceive('verifyAccount')->andReturn((object)[
                'status' => false,
            ]);
        });

        $this->user->wallet->withdrawable = 5000;
        $this->user->wallet->save();

        $response = $this->post(self::WITHDRAWAL_URL, [
            'account_number' => '124567890',
            'account_name' => 'Test User',
            'amount' => 500,
            'bank_name' => 'Test Bank'

        ]);

        $response->assertJson([
            'message' => 'Account is not valid'
        ]);
    }

    public function test_error_handled_when_withdrawal_initiation_goes_wrong_at_provider()
    {
        $this->mock(PaystackService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getBanks')->once()->andReturn(
                $this->banksMock
            );
            $mock->shouldReceive('verifyAccount')->andReturn((object)[
                'status' => true,
                'data' => (object)[
                    'account_name' => strtoupper($this->user->profile->first_name) . " " . strtoupper($this->user->profile->last_name)
                ]
            ]);
            $mock->shouldReceive('createTransferRecipient')->andReturn('randomrecipientcode');
            $mock->shouldReceive('initiateTransfer')->andThrowExceptions([new Exception()]);
        });

        $this->user->wallet->withdrawable = 5000;
        $this->user->wallet->save();

        $response = $this->post(self::WITHDRAWAL_URL, [
            'account_number' => '124567890',
            'account_name' => 'Test User',
            'amount' => 500,
            'bank_name' => 'Test Bank'

        ]);

        $response->assertJson([
            'message' => 'We are unable to complete your withdrawal request at this time, please try in a short while or contact support'
        ]);
    }

    public function test_that_a_deleted_account_cannot_make_withdrawal()
    {
        $this->user->delete();
        $response = $this->post(self::WITHDRAWAL_URL, [
            'account_number' => '124567890',
            'account_name' => 'Test User',
            'amount' => 500,
            'bank_name' => 'Test Bank'

        ]);

        $response->assertStatus(500);
    }
}
