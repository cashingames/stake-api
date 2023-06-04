<?php

namespace Tests\Feature;

use App\Enums\BonusType;
use App\Enums\FeatureFlags;
use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Models\Bonus;
use App\Models\GameSession;
use Tests\TestCase;
use App\Models\User;
use App\Models\UserBonus;
use Mockery\MockInterface;
use Illuminate\Support\Str;
use Database\Seeders\UserSeeder;
use App\Models\WalletTransaction;
use App\Services\FeatureFlag;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\WithFaker;
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

    public function test_that_a_user_cannot_withdraw_zero_naira()
    {
        $response = $this->post(self::WITHDRAWAL_URL, [
            'account_number' => '124567890',
            'account_name' => 'Test User',
            'amount' => 0,
            'bank_name' => 'Test Bank'

        ]);
        $response->assertJson([
            'message' => 'Invalid withdrawal amount. You can not withdraw NGN0',
        ]);
    }

    public function test_that_a_user_is_forced_to_verify_email_if_max_limit_is_exceeded()
    {

        Config::set('trivia.email_verification_limit_threshold', 10000);
        $this->user->update(['email_verified_at' => null]);

        $this->user->wallet->withdrawable = 20000;
        $this->user->wallet->save();

        WalletTransaction::create([
            'wallet_id' => $this->user->wallet->id,
            'transaction_type' => 'DEBIT',
            'amount' => 15000,
            'balance' => $this->user->wallet->withdrawable,
            'description' => 'Winnings Withdrawal Made',
            'reference' => Str::random(10),
            'created_at' => now()->subHours(3),
            'balance_type' => WalletBalanceType::WinningsBalance->value,
            'description_action' => WalletTransactionAction::WinningsWithdrawn->value
        ]);

        $response = $this->post(self::WITHDRAWAL_URL, [
            'account_number' => '124567890',
            'account_name' => 'Test User',
            'amount' => 0,
            'bank_name' => 'Test Bank'

        ]);

        $response->assertJsonFragment([
            'verifyEmailNavigation' => true,
        ]);
    }

    public function test_that_a_user_cannot_withdraw_less_than_configurable_one_time_minimum_withrawal_amount()
    {
        Config::set('trivia.staking.min_withdrawal_amount', 500);

        $this->user->wallet->withdrawable = 150;
        $this->user->wallet->save();


        $response = $this->post(self::WITHDRAWAL_URL, [
            'account_number' => '124567890',
            'account_name' => 'Test User',
            'amount' => 150,
            'bank_name' => 'Test Bank'

        ]);
        $response->assertJson([
            'message' => 'You can not withdraw less than NGN' . config('trivia.staking.min_withdrawal_amount'),
        ]);
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
            'description' => 'Winnings Withdrawal Made',
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

    public function test_that_user_cannot_withdraw_registration_bonus_win_if_he_did_not_get_minimum_perfect_scores()
    {
        config(['features.registration_bonus.enabled' => true]);
        config(['trivia.minimum_withdrawal_perfect_score_threshold' => 5]);

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

        UserBonus::create([
            'user_id' => $this->user->id,
            'bonus_id' =>  Bonus::where('name', BonusType::RegistrationBonus->value)->first()->id,
            'is_on' => true,
            'amount_credited' => 1500,
            'amount_remaining_after_staking' => 500,
            'total_amount_won'  => 1000,
            'amount_remaining_after_withdrawal' => 1000
        ]);

        $this->user->wallet->withdrawable = 1000;
        $this->user->wallet->save();

        $response = $this->post(self::WITHDRAWAL_URL, [
            'account_number' => '124567890',
            'account_name' => $this->user->profile->first_name . " " . $this->user->profile->last_name,
            'amount' => 1000,
            'bank_name' => 'Test Bank'

        ]);
        $response->assertJson([
            'message' => 'Sorry, you did not get up to ' . config('trivia.minimum_withdrawal_perfect_score_threshold') . ' perfect scores with registration bonus',
        ]);
    }

    public function test_that_user_can_withdraw_registration_bonus_win_if_he_gets_minimum_perfect_scores()
    {
        config(['features.registration_bonus.enabled' => true]);
        config(['trivia.minimum_withdrawal_perfect_score_threshold' => 5]);

        $this->seed(BonusSeeder::class);

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

        UserBonus::create([
            'user_id' => $this->user->id,
            'bonus_id' =>  Bonus::where('name', BonusType::RegistrationBonus->value)->first()->id,
            'is_on' => true,
            'amount_credited' => 1500,
            'amount_remaining_after_staking' => 500,
            'total_amount_won'  => 1000,
            'amount_remaining_after_withdrawal' => 1000
        ]);
        GameSession::factory()
            ->count(5)
            ->create();
        GameSession::query()->update(['user_id' => $this->user->id, 'correct_count' => 10]);
        $this->user->wallet->withdrawable = 1000;
        $this->user->wallet->save();

        $response = $this->post(self::WITHDRAWAL_URL, [
            'account_number' => '124567890',
            'account_name' => $this->user->profile->first_name . " " . $this->user->profile->last_name,
            'amount' => 1000,
            'bank_name' => 'Test Bank'

        ]);
        $response->assertJson([
            'message' => 'Your transfer is being successfully processed to your bank account',
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $this->user->id,
            'withdrawable' => 0
        ]);

        $this->assertDatabaseHas('user_bonuses', [
            'user_id' => $this->user->id,
            'total_amount_won'  => 1000,
            'amount_remaining_after_withdrawal' => 0
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
