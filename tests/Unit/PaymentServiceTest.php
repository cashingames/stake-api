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
    public $paymentService, $bankCode;
    public $accountNumber, $accountName, $banksMock;
    protected function setUp(): void
    {
        parent::setUp();
        config(['trivia.payment_key' => 'sk_test_f6d2a64925009ef4b4544c90b96ddcb3991fa0c3']);
        $this->mockGuzzleClient();
        $this->paymentService = $this->mockPaystackService();
        $this->bankCode = '078';
        $this->accountNumber = '1234567890';
        $this->accountName = 'Account Name';
        $this->banksMock = json_decode(json_encode([
            'data' => [
                [
                    'name' => 'Test Bank',
                    'code' => "059"
                ]
            ]
        ]));
        config(['trivia.payment_key' => 'sk_test_f6d2a64925009ef4b4544c90b96ddcb3991fa0c3']);
    }

    public function test_that_account_can_be_verified()
    {
        $data = [
            'status' => true,
            'data' => (object) [
                'account_name' => $this->accountName
            ]
        ];
        $this->paymentService->expects($this->once())
            ->method('verifyAccount')
            ->with($this->accountNumber,  $this->bankCode)
            ->willReturn($data);

        $result =  $this->paymentService->verifyAccount($this->accountNumber,  $this->bankCode);
        $this->assertEquals($result['data']->account_name, $this->accountName);
    }

    public function test_that_verify_account_can_fail()
    {   
        $service = new PaystackService();
        $result =  $service->verifyAccount($this->accountNumber,  $this->bankCode);

        $this->assertIsObject($result);
    }

    public function test_that_banks_can_fail()
    {   
        $service = new PaystackService();
        $result = $service->getBanks();

        $this->assertIsObject($result);
    }


    public function test_that_transfer_recipient_can_return_null()
    {
        $result =  $this->paymentService->createTransferRecipient($this->bankCode, $this->accountName, $this->accountNumber);

        $this->assertEquals($result, null);
    }


    public function test_that_transfer_recipient_can_be_created()
    {
        $this->paymentService->expects($this->once())
            ->method('createTransferRecipient')
            ->with($this->bankCode, $this->accountName, $this->accountNumber)
            ->willReturn('randomrecipientcode');

        $result =  $this->paymentService->createTransferRecipient($this->bankCode, $this->accountName, $this->accountNumber);

        $this->assertEquals($result, 'randomrecipientcode');
    }

    public function test_that_banks_can_be_fetched()
    {
        $this->paymentService->expects($this->once())
            ->method('getBanks')
            ->willReturn($this->banksMock);

        $result = $this->paymentService->getBanks();

        $this->assertEquals($result, $this->banksMock);
    }

    public function test_transfer_can_be_initiated()
    {
        $data = (object)[
            'status' => 'success',
            'reference' => 'randomref'
        ];
        $this->paymentService->expects($this->once())
            ->method('initiateTransfer')
            ->with('randomrecipientcode', 200)
            ->willReturn($data);

        $result = $this->paymentService->initiateTransfer('randomrecipientcode', 200);

        $this->assertEquals($result, $data);
    }

    public function test_transfer_initiated_url_can_be_called()
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

    public function test_verify_account_url_can_be_called()
    {
        $mock = new MockHandler([
            new Response(200, [
                'status' => true,
                'data' =>  [
                    'account_name' => $this->accountName
                ]
            ]),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $url = "https://api.paystack.co/bank/resolve?account_number=" . $this->accountNumber . "&bank_code=" . $this->bankCode;

        $response = $client->request('GET', $url);

        $this->assertEquals($response->getStatusCode(), 200);
    }

    public function test_transfer_recipient_url_can_be_called()
    {
        $mock = new MockHandler([
            new Response(200, ['recipientCode']),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $url = "https://api.paystack.co/transferrecipient";

        $response = $client->request('POST', $url, [
            'json' => [
                "type" => "nuban",
                "name" => $this->accountName,
                "account_number" => $this->accountNumber,
                "bank_code" => $this->bankCode,
                "currency" => "NGN"
            ]
        ]);

        $this->assertEquals($response->getStatusCode(), 200);
    }

    public function test_get_banks_url_can_be_called()
    {
        $mock = new MockHandler([
            new Response(200,  [

                'name' => 'Test Bank',
                'code' => "059"

            ]),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $url = 'https://api.paystack.co/bank';

        $response = $client->request('GET', $url);

        $this->assertEquals($response->getStatusCode(), 200);
    }

    private function mockGuzzleClient()
    {
        return $this->createMock(Client::class);
    }

    private function mockPaystackService()
    {
        return $this->createMock(PaystackService::class);
    }
}
