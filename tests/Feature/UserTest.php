<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Carbon;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    protected $user ;

    protected function setUp(): void
    {
        parent::setUp(); 
        $this->user = new User;
       
    }

    public function test_that_can_play_attribute_returns_true_if_campaign_is_on_and_time_is_within_campaign_time()
    {   
        config(['trivia.campaign.start_time' => Carbon::now()->subHour()]);
        config(['trivia.campaign.end_time' => Carbon::now()->addHours(5)]);

        $canPlay = $this->user->can_play;
        $this->assertTrue($canPlay);
    }

    public function test_that_can_play_attribute_returns_true_if_campaign_is_off()
    {   
       config(['trivia.campaign.enabled' => false]);
        
        $canPlay = $this->user->can_play;
        $this->assertTrue($canPlay);
    }

    // public function test_that_can_play_attribute_returns_false_if_time_is_not_within_time_range()
    // {   
    //     config(['trivia.campaign.start_time' => Carbon::now()->addHours(3)]);
    //     config(['trivia.campaign.end_time' => Carbon::now()->addHours(5)]);
      
    //     $canPlay = $this->user->can_play;
    //     $this->assertFalse($canPlay);
    // }
}
