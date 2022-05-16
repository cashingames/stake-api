<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use UserSeeder;
use PlanSeeder;

class ArtisanCommandsTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(PlanSeeder::class);
    }


    public function test_give_daily_bonus_command()
    {
        $this->artisan('bonus:daily-activate')->assertExitCode(0);
    }

    public function test_expire_daily_bonus_command()
    {
        $this->artisan('bonus:daily-expire')->assertExitCode(0);
    }
}
