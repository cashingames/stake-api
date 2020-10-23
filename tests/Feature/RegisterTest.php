<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{   
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // public function testExample()
    // {
    //     $response = $this->get('/');

    //     $response->assertStatus(200);
    // }

    /** @test */
    public function a_user_can_register()
    {   
        
        $response = $this->postjson('/api/auth/register',[
            'first_name' => 'User',
            'last_name'=>'Test',
            'username' => 'user',
            'phone' => '88838883838',
            'email' => 'user@user.com',
            'password' =>'password' ,
            'password_confirmation' => 'password'
           
        ]);
    
        $response->assertStatus(200);
       
        
    }
}
