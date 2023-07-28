<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class FrontendLoggingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_that_frontend_logs_is_written_into_file()
    {   
        
        $response = $this->post('/api/v3/log/frontend-info', [
            'message' => "test message",
            'data' => []
        ]);

        $logContents = file_get_contents(storage_path('logs/frontend/frontendLogs-'.now()->toDateString().'.log'));

        $this->assertStringContainsString('Frontend received:test message', $logContents);
       
        $response->assertOk();

    }

}
