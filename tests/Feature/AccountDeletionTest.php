<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->user = User::first();


        $this->actingAs($this->user);
    }

    public function test_that_an_account_can_be_deleted_with_post_method(){
    
        $this->post("/api/v3/account/delete");

        $this->assertSoftDeleted($this->user);
    }

    public function test_that_an_account_can_be_deleted_with_delete_method(){
    
        $this->delete("/api/v3/account/delete");

        $this->assertSoftDeleted($this->user);
    }

}
