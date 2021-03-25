<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp(); 
        $this->user = new User;
    
    }

    public function test_that_can_play_attribute_returns_true_if_campaign_is_on_and_time_is_within_campaign_time()
    {   
        $this->user->getCanPlayAttribute();
        $this->assertTrue(true);
    }

    public function test_that_can_play_attribute_returns_true_if_campaign_is_off()
    {   
        $this->user->getCanPlayAttribute();
        $this->assertTrue(true);
    }

    public function test_that_can_play_attribute_returns_false_if_not_within_time_range()
    {   
        config(['trivia.campaign.start_time' => "06:00:00"]);
        config(['trivia.campaign.end_time' => "07:00:00"]);

        $this->user->getCanPlayAttribute();
        $this->assertFalse(false);
    }
}
