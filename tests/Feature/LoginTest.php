<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use UserSeeder;

class LoginTest extends TestCase
{   
    // use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    /** @test */
    public function login_fields_cannot_be_empty()
    {   

        // $this->seed(UserSeeder::class);
        
        $response = $this->post('/api/auth/login',[
            "username" => " ",
            "password" => " ",
        ]);
        
        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Invalid username and password',
        ]);
       
        
    }

    /** @test */
    public function login_with_invalid_credentials()
    {   
        
        $response = $this->post('/api/auth/login',[
            "username" => " 399049039",
            "password" => " hhhfjffjjf",
        ]);
        
        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Invalid username and password',
        ]);
        
    }
     
}
