<?php

namespace Tests\Feature;

use App\Models\Profile;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(PlanSeeder::class);
        $this->user = User::first();

        $this->actingAs($this->user);
    }

    public function test_personal_details_can_be_edited()
    {

        $response = $this->postjson('/api/v2/profile/me/edit-personal', [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'username' => 'JayDee',
            'phoneNumber' => '09098989898',
            'email' => 'johndoe@email.com',
            'password' => 'password111',
            'gender' => 'male',
            'dateOfBirth' => '23-09-1998'
        ]);

        $response->assertStatus(200);
    }

    public function test_bank_details_can_be_edited()
    {

        $response = $this->postjson('/api/v2/profile/me/edit-bank', [
            'accountName' => 'John',
            'bankName' => 'Access bank',
            'accountNumber' => '09098989898',
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

    public function test_that_referrer_profile_returns_null_when_not_referrer_not_found()
    {

        //setup
        $profile = Profile::factory()->for(User::factory())->create([
            'referrer' => 'xxxxabc', //a non existing referrer
        ]);

        //act
        $response = $profile->getReferrerProfile();

        //assert


        $this->assertNull($response);
    }

    public function test_that_referrer_profile_returns_null_when_referrer_is_empty()
    {

        //setup
        $profile = Profile::factory()->for(User::factory())->create(); //no referrer populated

        //act
        $response = $profile->getReferrerProfile();

        //assert


        $this->assertNull($response);
    }

    public function test_that_referrer_profile_returns_value_when_referrer_is_a_valid_username()
    {

        //setup
        $newUser = User::factory()->create();
        $profile = Profile::factory()->for($newUser)->create([
            'referrer' => $this->user->username
        ]);

        //act
        $response = $profile->getReferrerProfile();

        //assert
        $this->assertEquals($response->first_name, $this->user->profile->first_name);
    }

    public function test_that_referrer_profile_returns_value_when_referrer_is_a_valid_referral_code()
    {

        //setup
        //user1 referred user 2 with old referral code format
        $newUser1 = User::factory()->create();
        $profile1 = Profile::factory()->for($newUser1)->create([
            'referral_code' => "random-referral-code"
        ]);

        $newUser2 = User::factory()->create();
        $profile2 = Profile::factory()->for($newUser2)->create([
            'referrer' => $profile1->referral_code
        ]);

        //act
        $response = $profile2->getReferrerProfile();

        //assert
        $this->assertEquals($response->first_name, $profile1->first_name);
    }
}
