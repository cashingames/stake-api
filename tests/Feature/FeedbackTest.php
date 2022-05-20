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
use Database\Seeders\NotificationSeeder;

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

    public function test_all_notifications_can_be_fetched(){
        
        $this->seed(UserSeeder::class);
        $this->seed(NotificationSeeder::class);

        $user = User::first();
        $this->actingAs($user);

        $response = $this->get("/api/v2/user/fetch/notifications");

        $response->assertStatus(200);
    }

    public function test_a_notification_can_be_read(){
        
        $this->seed(UserSeeder::class);
        $this->seed(NotificationSeeder::class);

        $user = User::first();
        $notification = Notification::first();

        $this->actingAs($user);

        $response = $this->post("/api/v2/user/read/notification/".$notification->id);

        $response->assertStatus(200);
    }

    public function test_all_notifications_can_be_read(){
        
        $this->seed(UserSeeder::class);
        $this->seed(NotificationSeeder::class);

        $user = User::first();
        Notification::first()->update(['user_id' => $user->id]);
    
        $this->actingAs($user);

        $response = $this->post("/api/v2/user/read/all/notifications");

        $response->assertStatus(200);
    }
}
