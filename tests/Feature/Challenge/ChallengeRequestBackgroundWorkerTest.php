<?php

namespace Tests\Feature\Challenge;

use App\Models\ChallengeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChallengeRequestBackgroundWorkerTest extends TestCase
{   
    use RefreshDatabase;
    public function test_that_hanging_challenge_requests_get_cleaned_up()
    {
        ChallengeRequest::factory()
            ->count(5)
            ->create(['created_at' => now()]);
        
        ChallengeRequest::where('id',3)->update(['created_at' => now()->subMinutes(5)]);
        
        $this->artisan('challenge-requests:clean-up')->assertExitCode(0);

        $this->assertDatabaseCount('challenge_requests', 4);
    }
}
