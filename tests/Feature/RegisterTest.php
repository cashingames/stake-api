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

    /** @test */
    public function a_user_cannot_register_with_empty_fields()
    {   
        
        $response = $this->postjson('/api/auth/register',[
            'first_name' => '',
            'last_name'=>'',
            'username' => '',
            'phone' => '',
            'email' => '',
            'password' =>'' ,
            'password_confirmation' => ''
            
        ]);
    
        $response->assertStatus(422);
        $response->assertJson([
            "message"=> "The given data was invalid.",
        ]);   
        $response->assertJsonFragment([
            'first_name' => ["The first name field is required."],
            'last_name' => ["The last name field is required."],
            'username' => ["The username field is required."],
            'phone' => ["The phone field is required."],
            'email' => ["The email field is required."],
            'password' =>["The password field is required."]
        ]);
        
    }

    /** @test */
    public function phone_number_cannot_be_less_than_11_digits()
    {   
        
        $response = $this->postjson('/api/auth/register',[
            'first_name' => 'User',
            'last_name'=>'Test',
            'username' => 'user',
            'phone' => '8883888383',
            'email' => 'user@user.com',
            'password' =>'password' ,
            'password_confirmation' => 'password'
            
        ]);
    
        $response->assertStatus(422);
        $response->assertJson([
            "message"=> "The given data was invalid.",
        ]); 
        $response->assertJsonFragment([
            'phone' => ['The phone must be at least 11 characters.']
        ]);
    }

    /** @test */
    public function email_field_must_accept_a_valid_email_format()
    {   
        
        $response = $this->postjson('/api/auth/register',[
            'first_name' => 'John',
            'last_name'=>'Doe',
            'username' => 'username',
            'phone' => '12345678909',
            'email' => 'user.com',
            'password' =>'password' ,
            'password_confirmation' => 'password'
            
        ]);
    
        $response->assertStatus(422);
        $response->assertJson([
            "message"=> "The given data was invalid.",
        ]); 
        $response->assertJsonFragment([
            'email' => ['The email must be a valid email address.']
        ]);
    }
    /** @test */
    public function firstname_and_lastname_cannot_be_numbers()
    {   
        
        $response = $this->postjson('/api/auth/register',[
            'first_name' => 102000300,
            'last_name'=>1009399494,
            'username' => 'user1',
            'phone' => '88838383844',
            'email' => 'email@user.com',
            'password' =>'password' ,
            'password_confirmation' => 'password'
           
        ]);
        $response->assertStatus(422);
        $response->assertJson([
            'errors' => [
                'first_name' => [ "The first name must be a string."],
                'last_name' =>  ["The last name must be a string."]
            ]
        ]);
    }

    /** @test */
    public function a_user_cannot_register_with_existing_username_email_or_phone()
    {   

        $this->a_user_can_register();
       
        $response = $this->postjson('/api/auth/register',[
            'first_name' => 'Jane',
            'last_name'=>'Doe',
            'username' => 'user',
            'phone' => '88838883838',
            'email' => 'user@user.com',
            'password' =>'password' ,
            'password_confirmation' => 'password'
           
        ]);
    
        $response->assertStatus(422);
        $response->assertJson([
            'errors' => [
                'username' => [ "The username has already been taken."],
                'phone' =>  ["The phone has already been taken."],
                'email' => ["The email has already been taken."]
            ]
        ]);
       
        
    }

}
