<?php

namespace Tests\Unit;

use App\Services\Payments\PaystackService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    public $paymentService;
    protected function setUp(): void
    {
        parent::setUp();
        config(['trivia.payment_key' => 'sk_test_f6d2a64925009ef4b4544c90b96ddcb3991fa0c3']);
        $this->mockGuzzleClient();
        $this->paymentService = new PaystackService();
    }

    public function test_that_account_can_be_verified()
    {
        $result =  $this->paymentService->verifyAccount('1234567890', '078');

        $this->assertIsObject($result);
    }

    public function test_that_transfer_recipient_can_be_created()
    {
        $result =  $this->paymentService->createTransferRecipient('078', 'Account Name', '1234567890');

        $this->assertEquals($result, null);
    }

    public function test_that_banks_can_be_fetched()
    {
        $result = $this->paymentService->getBanks();

        $this->assertIsObject($result);
    }

    public function test_transfer_can_be_initiated()
    {
        $mock = new MockHandler([
            new Response(200, [

                'status' => 'success',
                'reference' => 'randomref'
            ]),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $url = "https://api.paystack.co/transfer";
        $response = $client->request('POST', $url, [
            'json' => [
                'source' => "balance",
                'amount' => 100,
                'recipient' => 'randomrecipientcode',
                'reason' => "Winnings withdrawal made"
            ]
        ]);

        $this->assertEquals($response->getStatusCode(), 200);
    }

    private function mockGuzzleClient()
    {
        return $this->createMock(Client::class);
    }
}
