<?php

namespace Tests\Feature;

use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Jobs\SendAdminErrorEmailUpdate;
use App\Models\WalletTransaction;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\Models\User;
use Mockery\MockInterface;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Payments\PaystackService;
use Database\Seeders\BonusSeeder;
use Illuminate\Support\Facades\Queue;
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
            'amount' => 150,
            'bank_name' => 'Test Bank'
        ])->assertSessionHasErrors(['amount' => 'The amount must be at least 500.']);
    }

    /**
     * @dataProvider wrongAccountNamesDataProvider
     */
    public function test_that_a_user_cannot_withdraw_if_name_is_not_equal_to_account_name(
        $nameFromBank,
        $suppliedFirstName,
        $suppliedLastName
    )
    {
        $banksMock = $this->banksMock;
        $this->mock(PaystackService::class, function (MockInterface $mock) use ($banksMock, $nameFromBank) {
            $mock->shouldReceive('getBanks')->once()->andReturn(
                $banksMock
            );
            $mock->shouldReceive('verifyAccount')->andReturn((object)[
                'status' => true,
                'data' => (object)[
                    'account_name' => $nameFromBank
                ]
            ]);
            $mock->shouldReceive('createTransferRecipient')->andReturn('randomrecipientcode');
            $mock->shouldReceive('initiateTransfer')->andReturn((object)[
                'status' => 'success',
                'reference' => 'randomref'
            ]);
        });
        Queue::fake();
        $this->user->profile->update([
            'first_name' => $suppliedFirstName,
            'last_name' => $suppliedLastName,
        ]);
        $this->user->wallet->update([
            'withdrawable' => 1000
        ]);

        $response = $this->post(self::WITHDRAWAL_URL, [
            'account_number' => '124567890',
            'amount' => 500,
            'bank_name' => 'Test Bank'
        ]);

        Queue::assertPushed(SendAdminErrorEmailUpdate::class);

        $response->assertJson([
            'message' => 'Account name does not match your registration name. Please contact support to assist.',
        ]);
    }

    /**
     * @dataProvider correctAccountNamesDataProvider
     */
    public function test_that_user_can_withdraw_successfully(
        $nameFromBank,
        $suppliedFirstName,
        $suppliedLastName
    )
    {
        $banksMock = $this->banksMock;
        $this->mock(PaystackService::class, function (MockInterface $mock) use ($banksMock, $nameFromBank) {
            $mock->shouldReceive('getBanks')->once()->andReturn(
                $banksMock
            );
            $mock->shouldReceive('verifyAccount')->andReturn((object)[
                'status' => true,
                'data' => (object)[
                    'account_name' => $nameFromBank
                ]
            ]);

            $mock->shouldReceive('createTransferRecipient')->andReturn('randomrecipientcode');
            $mock->shouldReceive('initiateTransfer')->andReturn((object)[
                'status' => 'success',
                'reference' => 'randomref'
            ]);
        });

        $this->user->profile->update([
            'first_name' => $suppliedFirstName,
            'last_name' => $suppliedLastName,
        ]);

        $this->user->wallet->update([
            'withdrawable' => 5000
        ]);

        $response = $this->post(self::WITHDRAWAL_URL, [
            'account_number' => '124567890',
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
            'balance' => 4500
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
                    'account_name' => strtoupper($this->user->profile->full_name)
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
                    'account_name' => strtoupper($this->user->profile->full_name)
                ]
            ]);
            $mock->shouldReceive('createTransferRecipient')->andReturn('randomrecipientcode');
            $mock->shouldReceive('initiateTransfer')->andThrowExceptions([new Exception()]);
        });

        $this->user->wallet->withdrawable = 5000;
        $this->user->wallet->save();

        $response = $this->post(self::WITHDRAWAL_URL, [
            'account_number' => '124567890',
            'amount' => 500,
            'bank_name' => 'Test Bank'

        ]);

        $response->assertJson([
            'message' => 'We are unable to complete your withdrawal request at' .
            ' this time, please try in a short while or contact support'
        ]);
    }

    public function test_that_a_deleted_account_cannot_make_withdrawal()
    {
        $this->user->delete();
        $response = $this->post(self::WITHDRAWAL_URL, [
            'account_number' => '124567890',
            'amount' => 500,
            'bank_name' => 'Test Bank'

        ]);

        $response->assertStatus(500);
    }

    public function test_that_a_user_is_forced_to_verify_if_max_withdrawal_limit_is_exceeded()
    {

        Config::set('trivia.max_withdrawal_amount', 10000);

        $this->user->wallet->withdrawable = 20000;
        $this->user->wallet->save();

        WalletTransaction::create([
            'wallet_id' => $this->user->wallet->id,
            'transaction_type' => 'CREDIT',
            'amount' => 15000,
            'balance' => $this->user->wallet->withdrawable,
            'description' => 'Wallet Top-up',
            'reference' => Str::random(10),
            'created_at' => now()->subHours(3),
            'balance_type' => WalletBalanceType::WinningsBalance->value,
            'transaction_action' => WalletTransactionAction::WinningsWithdrawn->value
        ]);

        $response = $this->post(self::WITHDRAWAL_URL, [
            'account_number' => '124567890',
            'amount' => 500,
            'bank_name' => 'Test Bank'
        ]);

        $response->assertJsonFragment([
            'message' => 'Please contact support to verify your identity to proceed with this withdrawal'
        ]);
    }


    public function test_that_a_user_can_withdraw_max_withdrawal_limit_if_verified()
    {

        Config::set('trivia.max_withdrawal_amount', 10000);

        $banksMock = $this->banksMock;
        $this->mock(PaystackService::class, function (MockInterface $mock) use ($banksMock) {
            $mock->shouldReceive('getBanks')->once()->andReturn(
                $banksMock
            );
            $mock->shouldReceive('verifyAccount')->andReturn((object)[
                'status' => true,
                'data' => (object)[
                    'account_name' => strtoupper($this->user->profile->full_name)
                ]
            ]);
            $mock->shouldReceive('createTransferRecipient')->andReturn('randomrecipientcode');
            $mock->shouldReceive('initiateTransfer')->andReturn((object)[
                'status' => 'pending',
                'reference' => 'randomref'
            ]);
        });
        
        $this->user->meta_data = [
            'kyc_verified' => true
        ];
        $this->user->save();

        $this->user->wallet->withdrawable = 20000;
        $this->user->wallet->save();

        WalletTransaction::create([
            'wallet_id' => $this->user->wallet->id,
            'transaction_type' => 'CREDIT',
            'amount' => 15000,
            'balance' => $this->user->wallet->withdrawable,
            'description' => 'Wallet Top-up',
            'reference' => Str::random(10),
            'created_at' => now()->subHours(3),
            'balance_type' => WalletBalanceType::WinningsBalance->value,
            'transaction_action' => WalletTransactionAction::WinningsWithdrawn->value
        ]);

        $response = $this->post(self::WITHDRAWAL_URL, [
            'account_number' => '124567890',
            'amount' => 500,
            'bank_name' => 'Test Bank'
        ]);

        $response->assertJson([
            'message' => 'Transfer processing, wait for your bank account to reflect'
        ]);
    }

    public function test_that_a_user_cannot_withdraw_more_than_exceeded_amount_if_not_verified()
    {
        Config::set('trivia.max_withdrawal_amount', 10000);
        $this->user->meta_data = [
            'kyc_verified' => false
        ];
        $this->user->save();

        $this->user->wallet->withdrawable = 20000;
        $this->user->wallet->save();

        WalletTransaction::create([
            'wallet_id' => $this->user->wallet->id,
            'transaction_type' => 'CREDIT',
            'amount' => 15000,
            'balance' => $this->user->wallet->withdrawable,
            'description' => 'Wallet Top-up',
            'reference' => Str::random(10),
            'created_at' => now()->subHours(3),
            'balance_type' => WalletBalanceType::WinningsBalance->value,
            'transaction_action' => WalletTransactionAction::WinningsWithdrawn->value
        ]);

        $response = $this->post(self::WITHDRAWAL_URL, [
            'account_number' => '124567890',
            'amount' => 500,
            'bank_name' => 'Test Bank'
        ]);

        $response->assertJsonFragment([
            'message' => 'Please contact support to verify your identity to proceed with this withdrawal'
        ]);
    }


    public function wrongAccountNamesDataProvider()
    {
        // name from bank, supplied first name, supplied last name
        return [
            ['Oyesola Ogundele', 'Oyesola', 'Akinkunmi'],
            ['JUWA BABAFEMI', 'BENJAMIN JUWA', 'Enitan'],
            ['MUHAMMED-BASHEER AISHAT', 'Aisha', 'Muhammed']
        ];
    }

    public function correctAccountNamesDataProvider()
    {
        // name from bank, supplied first name, supplied last name
        return [
            ['OLUWATOYOSI MARVELLOUS ERAIYETAN', 'OLUWATOYOSI MARVELOUS', 'ERAIYETAN'],
            ['JUWA BABAFEMI', 'JUWA', 'BABAFEMI'],
            ['MUHAMMED-BASHEER AISHAT', 'Aishat', 'Muhammed']
        ];
    }
}
