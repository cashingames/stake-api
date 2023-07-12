<?php

namespace Tests\Feature;

use App\Enums\BonusType;
use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Models\Bonus;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use UserSeeder;
use App\Models\User;
use App\Models\UserBonus;
use App\Models\WalletTransaction;
use Database\Seeders\BonusSeeder;
use PHPUnit\Framework\Constraint\IsFalse;

class ProfileTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    const PROFILE_DATA_URL = '/api/v3/user/profile';
    const CHANGE_PASSWORD_URL = '/api/v3/profile/me/password/change';
    const EDIT_PROFILE_URL = '/api/v3/profile/me/edit-personal';

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->user = User::first();

        $this->actingAs($this->user);
    }

    public function test_relevant_user_profile_can_be_retrieved_with_data()
    {   
        $this->seed(BonusSeeder::class);
        $response = $this->withHeaders([
            'x-brand-id' => 1,
        ])->get(self::PROFILE_DATA_URL);

        $response->assertJson([
            'data' => [
                'username' => $this->user->username,
                'email' => $this->user->email,
                'lastName' => $this->user->profile->last_name,
                'firstName' => $this->user->profile->first_name,
                'countryCode' => $this->user->country_code,
                'phoneNumber' => $this->user->phone_number,
                'walletBalance' => $this->user->wallet->non_withdrawable + $this->user->wallet->withdrawable,
                'bonusBalance' => $this->user->wallet->bonus_balance,
                'withdrawableBalance' => $this->user->wallet->withdrawable,
                'boosts' => [],
            ]
        ]);
    }

    public function test_personal_details_can_be_edited_with_all_fields_set()
    {

        $this->postjson(self::EDIT_PROFILE_URL, [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'username' => 'JayDee',
            'phoneNumber' => '09098989898',
            'email' => 'johndoe@email.com',
            'password' => 'password111',
            'gender' => 'male',
            'dateOfBirth' => '23-09-1998'
        ]);
        $this->assertEquals(($this->user->profile->first_name . ' ' . $this->user->profile->last_name), 'John Doe');
    }

    public function test_username_can_be_edited()
    {

        $this->postjson(self::EDIT_PROFILE_URL, [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'username' => 'JayDee',
            'phoneNumber' => '09098989898',
            'email' => 'johndoe@email.com',
            'password' => 'password111',
            'gender' => 'male',
            'dateOfBirth' => '23-09-1998'
        ]);
        $this->assertEquals($this->user->username, 'JayDee');
    }

    public function test_email_can_be_edited()
    {

        $this->postjson(self::EDIT_PROFILE_URL, [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'username' => 'JayDee',
            'phoneNumber' => '09098989898',
            'email' => 'johndoe@email.com',
            'password' => 'password111',
            'gender' => 'male',
            'dateOfBirth' => '23-09-1998'
        ]);
        $this->assertEquals($this->user->email, 'johndoe@email.com');
    }



    public function test_profile_image_can_be_uploaded()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->post('/api/v3/profile/me/picture', [
            'avatar' => $file,
        ]);

        $avatar = $this->user->profile->avatar;

        $this->assertTrue(!is_null($avatar));

        Storage::disk('avatars')->delete($file->hashName());
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

    public function test_that_password_can_be_changed()
    {
        $response = $this->postjson(self::CHANGE_PASSWORD_URL, [
            'password' => 'password',
            'new_password' => 'password123',
            'new_password_confirmation' => 'password123'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Password Changed!.',
        ]);
    }

    public function test_that_old_password_must_be_correct_before_being_changed()
    {
        $response = $this->postjson(self::CHANGE_PASSWORD_URL, [
            'password' => 'password111',
            'new_password' => 'password123',
            'new_password_confirmation' => 'password123'
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Old password inputed does not match existing password.',
        ]);
    }

    public function test_that_old_password_must_differ_from_new_password()
    {
        $response = $this->postjson(self::CHANGE_PASSWORD_URL, [
            'password' => 'password111',
            'new_password' => 'password111',
            'new_password_confirmation' => 'password111'
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'The new password must be different from the old password.',
        ]);
    }

    public function test_reg_bonus_popup_toggle_returns_true_if_user_has_not_funded_before_and_has_reg_bonus()
    {
        $this->seed(BonusSeeder::class);
        UserBonus::create([
            'user_id' => $this->user->id,
            'bonus_id' =>  Bonus::where('name', BonusType::RegistrationBonus->value)->first()->id,
            'is_on' => true,
            'amount_credited' => 0,
            'amount_remaining_after_staking' => 500,
            'total_amount_won'  => 0,
            'amount_remaining_after_withdrawal' => 0
        ]);
        $response = $this->get(self::PROFILE_DATA_URL);

        $response->assertJson([
            'data' => [
                'showRegistrationBonusNotice' => true,
            ]
        ]);
    }

    public function test_reg_bonus_popup_toggle_returns_false_if_user_has_not_funded_before_and_does_not_have_reg_bonus()
    {
        $this->seed(BonusSeeder::class);
        UserBonus::create([
            'user_id' => $this->user->id,
            'bonus_id' =>  Bonus::where('name', BonusType::RegistrationBonus->value)->first()->id,
            'is_on' => false,
            'amount_credited' => 0,
            'amount_remaining_after_staking' => 500,
            'total_amount_won'  => 0,
            'amount_remaining_after_withdrawal' => 0
        ]);
        $response = $this->get(self::PROFILE_DATA_URL);

        $response->assertJson([
            'data' => [
                'showRegistrationBonusNotice' => false,
            ]
        ]);
    }

    public function test_registration_bonus_popup_toggle_returns_false_if_user_has_funded_before()
    {
        WalletTransaction::create([
            'wallet_id' => $this->user->wallet->id,
            'transaction_type' => 'CREDIT',
            'amount' => 200,
            'balance' => 200,
            'description' => 'Wallet Top-up',
            'reference' =>  'thsuu883930304900jdk',
            'balance_type' => WalletBalanceType::CreditsBalance->value,
            'transaction_action' => WalletTransactionAction::WalletFunded->value
        ]);

        $response = $this->get(self::PROFILE_DATA_URL);

        $response->assertJson([
            'data' => [
                'showRegistrationBonusNotice' => false,
            ]
        ]);
    }
}
