<?php

namespace Tests\Feature;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{   

    public function test_that_payment_webhook_will_not_proceed_with_wrong_ip()
    {   
        $response = $this->post('/api/v3/paystack/transaction/avjshasahnmsa');

        $response->assertStatus(200);
    }

    public function test_that_payment_webhook_does_not_proceed_with_invalid_key()
    {   
        $response = $this->withServerVariables(['REMOTE_ADDR' => '52.214.14.220'])
        ->post('/api/v3/paystack/transaction/avjshasahnmsa');

        $logContents = file_get_contents(storage_path('logs/laravel-'.now()->toDateString().'.log'));

        $this->assertStringContainsString('paystack call made with invalid key', $logContents);

        $response->assertStatus(200);
    }

}
