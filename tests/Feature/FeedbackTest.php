<?php

namespace Tests\Feature;

use App\Mail\Feedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;

class FeedbackTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_a_feedback_can_be_sent()
    {
        Mail::fake();
        
        $response = $this->post('/api/v2/client/feedback',[
            "first_name" => "Test",
            "last_name" => "User",
            "email" => "email@user.com",
            "message_body"=> "Lorem Ipsum dorem bla bla bla"
        ]);

        Mail::assertSent(Feedback::class);

        $response->assertStatus(200);
    }
}
