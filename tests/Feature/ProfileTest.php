<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use UserSeeder;
use PlanSeeder;
use App\Models\User;

class ProfileTest extends TestCase
{   
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    protected $user;

    protected function setUp(): void{
        parent::setUp();
        
        $this->seed(UserSeeder::class);
        $this->seed(PlanSeeder::class);
        $this->user = User::first(); 

        $this->actingAs($this->user);

    }

    public function test_personal_details_can_be_edited(){
       
        $response = $this->postjson('/api/v2/profile/me/edit-personal',[
            'firstName'=>'John',
            'lastName' => 'Doe',
            'username' => 'JayDee',
            'phoneNumber'=>'09098989898',
            'email'=>'johndoe@email.com',
            'password'=>'password111',
            'gender' => 'male',
            'dateOfBirth' => '23-09-1998'
        ]);
        
        $response->dump();
        $response->assertStatus(200);
    }

    public function test_bank_details_can_be_edited(){
       
        $response = $this->postjson('/api/v2/profile/me/edit-bank',[
            'accountName'=>'John',
            'bankName' => 'Access bank',
            'accountNumber'=>'09098989898',
        ]);
      
        $response->assertStatus(200);
    }

    public function test_profile_image_can_be_uploaded()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->post('/api/v2/profile/me/picture', [
            'avatar' => $file,
        ]);

        
        $response->assertStatus(200);
    }

}
