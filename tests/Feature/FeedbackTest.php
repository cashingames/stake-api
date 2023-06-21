<?php

namespace Tests\Feature;

use App\Mail\Feedback;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;


class FeedbackTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_a_feedback_can_be_sent()
    {
        Mail::fake();

        $response = $this->post('/api/v2/client/feedback', [
            "first_name" => "Test",
            "last_name" => "User",
            'phone_number' => '07039999999',
            "email" => "email@user.com",
            "message_body" => "Lorem Ipsum dorem bla bla bla"
        ]);

        Mail::assertSent(Feedback::class);

        $response->assertStatus(200);
    }

    public function test_a_feedback_cannot_be_sent_with_empty_fields()
    {
        Mail::fake();

        $this->post('/api/v2/client/feedback', [
            "first_name" => "",
            "last_name" => "",
            "email" => "",
            "message_body" => ""
        ]);

        Mail::assertNotSent(Feedback::class);
    }

    public function test_a_feedback_can_be_sent_without_first_name_and_last_name()
    {
        Mail::fake();

        $this->post('/api/v2/client/feedback', [
            "email" => 'email@email.com',
            "message_body" => "lorem ipsum "
        ]);

        Mail::assertSent(Feedback::class);
    }

    public function test_a_support_ticket_can_be_sent()
    {
        Http::fake();
        Config::set('app.osticket_support_key', '6117A948C970E5D7B3AC5B3E706E2666');
        Config::set('app.osticket_support_key', 'https://support.cashingames.com/ostic/api/tickets.json');

        $response = Http::withHeaders([
            'X-API-Key' => config('app.osticket_support_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post(config('app.osticket_support_url'), [
            'name'     =>     'Test User',
            'email'    =>     'user@email.com',
            'phone'     => '07030004949',
            'subject'   =>      'Inquiry/Complaint',
            'message'   =>   'testing feedback',
            'topicId'   =>      '1',
            'attachments' => array()
        ]);

        $this->assertEquals($response->getStatusCode(), 200);
    }

    public function test_faq_and_answers_can_be_fetched()
    {
        $response = $this->get('/api/v2/faq/fetch');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'question',
                    'answer',
                ]
            ]
        ]);
    }
}
