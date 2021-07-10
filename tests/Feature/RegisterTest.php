<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\DatabaseSeeder;
use UserSeeder;
use App\Models\User;

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

    protected $user;

    protected function setUp(): void{
        parent::setUp();
        $this->seed(UserSeeder::class);
        $this->user = User::first();
    
    }
    
    /** @test */
    public function a_user_can_register()
    {   
        $response = $this->postjson('/api/auth/register',[
            'first_name' => 'User',
            'last_name'=>'Test',
            'username' => 'user',
            'phone_number' => '88838883838',
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
            'phone_number' => '',
            'email' => '',
            'password' =>'' ,
            'password_confirmation' => ''
            
        ]);
    
        $response->assertStatus(422);
        
    }

    /** @test */
    public function phone_number_cannot_be_less_than_11_digits()
    {   
        
        $response = $this->postjson('/api/auth/register',[
            'first_name' => 'User',
            'last_name'=>'Test',
            'username' => 'user',
            'phone_number' => '8883888383',
            'email' => 'user@user.com',
            'password' =>'password' ,
            'password_confirmation' => 'password'
            
        ]);
    
        $response->assertStatus(422);

    }

    /** @test */
    public function email_field_must_accept_a_valid_email_format()
    {   
        
        $response = $this->postjson('/api/auth/register',[
            'first_name' => 'John',
            'last_name'=>'Doe',
            'username' => 'username',
            'phone_number' => '12345678909',
            'email' => 'user.com',
            'password' =>'password' ,
            'password_confirmation' => 'password'
            
        ]);
    
        $response->assertStatus(422);
 
    }
    /** @test */
    public function firstname_and_lastname_cannot_be_numbers()
    {   
        
        $response = $this->postjson('/api/auth/register',[
            'first_name' => 102000300,
            'last_name'=>1009399494,
            'username' => 'user1',
            'phone_number' => '88838383844',
            'email' => 'email@user.com',
            'password' =>'password' ,
            'password_confirmation' => 'password'
           
        ]);
        $response->assertStatus(422);

    }

    /** @test */
    public function a_user_cannot_register_with_existing_username_email_or_phone_number()
    {
       
        $response = $this->postjson('/api/auth/register',[
            'first_name' => 'Jane',
            'last_name'=>'Doe',
            'username' => $this->user->username,
            'phone_number' => $this->user->phone_number,
            'email' => $this->user->email,
            'password' =>'password' ,
            'password_confirmation' => 'password'
           
        ]);
        
        $response->assertStatus(422);
       
        
    }

}
