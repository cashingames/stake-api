<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\GameSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BubbleBlitzGameModesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
        $this->user = User::first();
        $this->actingAs($this->user);
    }

    public function test_that_bubble_blitz_game_modes_endpoint_is_successful()
    {
        $response = $this->get('/api/v3/bubble-blitz/modes');
        $response->assertStatus(200);
    }
}
