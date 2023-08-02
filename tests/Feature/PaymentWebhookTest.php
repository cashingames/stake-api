<?php

namespace Tests\Feature;

use App\Actions\Boosts\BuyBoostAction;
use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Enums\WalletTransactionType;
use App\Http\Controllers\WalletController;
use App\Models\User;
use App\Repositories\Cashingames\BoostRepository;
use App\Repositories\Cashingames\WalletRepository;
use App\Repositories\Cashingames\WalletTransactionDto;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\Request;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;
    protected $user;

    const WEBHOOK_URL = '/api/v3/paystack/transaction/avjshasahnmsa';
    const VERIFY_TRANSACTION_URL = '/api/v3/wallet/me/transaction/verify/randomReference';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->user = User::first();

        $this->actingAs($this->user);
        config(['trivia.payment_key' => 'sk_test_f6d2a64925009ef4b4544c90b96ddcb3991fa0c3']);
    }


    public function test_that_payment_webhook_will_not_proceed_with_wrong_ip()
    {
        $response = $this->post(self::WEBHOOK_URL);

        $response->assertStatus(200);
    }

    public function test_that_payment_webhook_does_not_proceed_with_invalid_key()
    {
        $response = $this->withServerVariables(['REMOTE_ADDR' => '52.214.14.220'])
            ->post(self::WEBHOOK_URL);

        $logContents = file_get_contents(storage_path('logs/laravel-' . now()->toDateString() . '.log'));

        $this->assertStringContainsString('paystack call made with invalid key', $logContents);

        $response->assertStatus(200);
    }

    public function test_that_transaction_verify_end_point_works()
    {
        $response = $this->get(self::VERIFY_TRANSACTION_URL);

        $response->assertStatus(200);
    }

    public function test_wallet_transaction_is_created_when_wallet_is_funded()
    {
        config(['trivia.bonus.signup.registration_bonus_percentage' => 10000]);

        $boostRepository = new BoostRepository();
        $walletRepository = new WalletRepository();
        $buyBoostAction = new BuyBoostAction($boostRepository, $walletRepository);

        (new WalletControllerTestStub(
            $boostRepository,
            $walletRepository,
            $buyBoostAction
        ))->handleChargeSuccess();


        $this->assertDatabaseHas('wallets', [
            'user_id' =>  $this->user->id,
            'bonus' => 200,
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' =>  $this->user->wallet->id,
            'amount' => 200,
        ]);
    }

}
class WalletControllerTestStub extends WalletController
{
    public function __construct(
        BoostRepository $boostRepository,
        WalletRepository $walletRepository,
        BuyBoostAction $buyBoostAction
    ) {
        parent::__construct($boostRepository, $walletRepository, $buyBoostAction);
    }

    public function handleChargeSuccess()
    {
        return $this->savePaymentTransaction('12345xyzuvwabcdef', $this->user->email, 200);
    }
}
