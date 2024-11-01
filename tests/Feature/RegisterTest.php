<?php

namespace Tests\Feature;

use App\Enums\BonusType;
use BoostSeeder;
use Tests\TestCase;
use App\Models\User;
use App\Models\Bonus;
use Mockery\MockInterface;
use Illuminate\Support\Facades\Mail;
use App\Services\SMS\SMSProviderInterface;
use Database\Seeders\BonusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;


    protected $user;
    const REGISTER_URL = '/api/auth/register';
    const RESEND_OTP_URL = '/api/auth/register/token/resend';

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()
            ->count(1)
            ->create();
        $this->seed(BoostSeeder::class);
        $this->user = User::first();
        Mail::fake();
        config(['services.termii.api_key' => 'termii_api_key']);
    }


    /** @test */
    public function a_user_cannot_register_with_empty_fields()
    {

        $response = $this->postjson(self::REGISTER_URL, [
            'first_name' => '',
            'last_name' => '',
            'username' => '',
            'country_code' => '',
            'phone_number' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => ''

        ]);

        $response->assertStatus(422);
    }

    /** @test */
    // public function country_code_is_required_for_phone_numbers()
    // {

    //     $response = $this->postjson(self::REGISTER_URL, [
    //         'first_name' => 'User',
    //         'last_name' => 'Test',
    //         'username' => 'user',
    //         'phone_number' => '8883888383',
    //         'email' => 'user@user.com',
    //         'password' => 'password',
    //         'password_confirmation' => 'password'

    //     ]);

    //     $response->assertJson([
    //         'message' => 'The country code field is required.',
    //     ]);
    // }

    /** @test */
    public function email_field_must_accept_a_valid_email_format()
    {

        $response = $this->postjson(self::REGISTER_URL, [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'username',
            'country_code' => '234',
            'phone_number' => '12345678909',
            'email' => 'user.com',
            'password' => 'password',
            'password_confirmation' => 'password'

        ]);

        $response->assertStatus(422);
    }
    /** @test */
    public function firstname_and_lastname_cannot_be_numbers()
    {

        $response = $this->postjson(self::REGISTER_URL, [
            'first_name' => 102000300,
            'last_name' => 1009399494,
            'username' => 'user1',
            'phone_number' => '88838383844',
            'email' => 'email@user.com',
            'password' => 'password',
            'password_confirmation' => 'password'

        ]);
        $response->assertStatus(422);
    }

    /** @test */
    public function username_cannot_contain_special_characters()
    {

        $response = $this->postjson(self::REGISTER_URL, [
            'first_name' => "New",
            'last_name' => "User",
            'username' => 'user@$*1',
            'phone_number' => '88838383844',
            'email' => 'email@user.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'country_code' => '+234'

        ]);
        $response->assertJsonFragment([
            'message' => 'The username must only contain letters and numbers.',
        ]);
    }

    /** @test */
    public function a_user_cannot_register_with_existing_username_email_or_phone_number()
    {

        $response = $this->postjson(self::REGISTER_URL, [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'username' => $this->user->username,
            'phone_number' => $this->user->phone_number,
            'email' => $this->user->email,
            'password' => 'password',
            'password_confirmation' => 'password'

        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function a_phone_number_variant_is_considered_a_duplicate_phone_number()
    {
        $this->user->update(['phone_number' => 704995878]);

        $response = $this->postjson(self::REGISTER_URL, [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'username' => 'jaydoe',
            'country_code' => '+234',
            'phone_number' => '0' . $this->user->phone_number,
            'email' => 'email@email.com',
            'password' => 'password',
            'password_confirmation' => 'password'

        ]);



        $response->assertJson([
            'message' => 'The phone number has been taken, contact support',
        ]);
    }

    public function test_a_user_recieves_sms_otp_on_registration()
    {
        $this->mock(SMSProviderInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('deliverOTP')->once();
        });
        config(['features.phone_verification.enabled' => true]);
        $response = $this->postjson(self::REGISTER_URL, [
            'first_name' => 'User',
            'last_name' => 'Test',
            'username' => 'userotp',
            'country_code' => '+234',
            'phone_number' => '88838883838',
            'email' => 'user@user.com',
            'password' => 'password',
            'password_confirmation' => 'password'

        ]);

        $response->assertOk();
    }

    public function test_a_user_can_register_from_web_without_needing_email_verification()
    {

        $this->mock(SMSProviderInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('deliverOTP')->once();
        });

        $response = $this->withHeaders([
            'X-App-Source' => 'web',
        ])->postjson(self::REGISTER_URL, [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'username' => 'janeDoe',
            'country_code' => '+234',
            'phone_number' => '08012345678',
            'email' => 'jane@doe.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);


        $response->assertOk();
    }

    public function test_brand_id_can_be_inserted_stakers_app()
    {

        $this->withHeaders(['x-brand-id' => 2])->postjson(self::REGISTER_URL, [
            'first_name' => 'Jane',
            'last_name' => "Doe",
            'username' => 'janeJoe',
            'country_code' => '+234',
            'phone_number' => '7098498884',
            'email' => 'email@email.com',
            'password' => 'password',
            'password_confirmation' => 'password'

        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'janeJoe',
            'phone_number' => '7098498884',
            'email' => 'email@email.com',
            'country_code' => '+234',
            'brand_id' => 2
        ]);
    }

    public function test_brand_id_can_be_inserted_in_fun_app()
    {

        $this->postjson(self::REGISTER_URL, [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'username' => 'janeDoe',
            'country_code' => '+234',
            'phone_number' => '7098498884',
            'email' => 'email@email.com',
            'password' => 'password',
            'password_confirmation' => 'password'

        ]);

        $this->assertDatabaseHas('users', [
            'phone_number' => '7098498884',
            'email' => 'email@email.com',
            'country_code' => '+234',
            'brand_id' => 1
        ]);
    }

    public function test_that_meta_details_can_be_created_for_user()
    {

        $this->postjson(self::REGISTER_URL, [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'username' => 'janeDoe',
            'country_code' => '+234',
            'phone_number' => '7098498884',
            'email' => 'email@email.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'device_model' => "Camon",
            'device_brand' => "Tecno",
            'device_token' => "255366367484THD"

        ]);

        $this->assertDatabaseHas('users', [
            'meta_data->device_model' => "Camon",
            'meta_data->device_brand' => "Tecno",
            'meta_data->device_token' => "255366367484THD",
            'meta_data->kyc_verified' => false,
            'meta_data->registration_ip_address' => request()->ip(),
        ]);
    }
}
