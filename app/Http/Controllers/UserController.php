<?php

namespace App\Http\Controllers;

use App\Enums\WalletTransactionAction;
use App\Models\User;
use App\Services\Bonuses\RegistrationBonus\RegistrationBonusService;
use Illuminate\Support\Carbon;
use App\Http\ResponseHelpers\CommonDataResponse;
use stdClass;

class UserController extends BaseController
{

    public function profile()
    {
        $this->user->load(['profile', 'wallet']);

        $result = new stdClass;
        $result->username = $this->user->username;
        $result->email = $this->user->email;
        $result->lastName = $this->user->profile->last_name;
        $result->firstName = $this->user->profile->first_name;
        $result->joinedOn = Carbon::parse($this->user->created_at)->toDateTimeString();
        $result->fullName = $this->user->profile->full_name;
        $result->countryCode = $this->user->country_code;
        $result->phoneNumber = $this->user->phone_number;
        $result->bankName = $this->user->profile->bank_name;
        $result->accountName = $this->user->profile->account_name;
        $result->accountNumber = $this->user->profile->account_number;
        $result->dateOfBirth = $this->user->profile->date_of_birth;
        $result->gender = $this->user->profile->gender;
        $result->avatar = $this->user->profile->avatar;
        $result->referralCode = $this->user->username;
        $result->walletBalance = $this->user->wallet->non_withdrawable + $this->user->wallet->withdrawable;
        $result->bonusBalance = $this->user->wallet->bonus;
        $result->hasBonus = $this->user->wallet->hasBonus();
        $result->showRegistrationBonusNotice = $this->shouldShowRegistrationBonusNotice($this->user);
        $result->withdrawableBalance = $this->user->wallet->withdrawable;
        $result->isEmailVerified = is_null($this->user->email_verified_at) ? false : true;
        $result->isPhoneVerified = is_null($this->user->phone_verified_at) ? false : true;
        $result->unreadNotificationsCount = $this->user->getUnreadNotificationsCount();
        $result->boosts = $this->user->userBoosts();

        $result = (new CommonDataResponse())->transform($result);

        return $this->sendResponse($result, "User details");
    }
    public function deleteAccount()
    {
        $user = User::find($this->user->id);

        if (is_null($user)) {
            return $this->sendResponse('Your Account has been deleted', 'Your Account has been deleted');
        }

        $user->delete();
        auth()->logout(true);

        return $this->sendResponse('Your Account has been deleted', 'Your Account has been deleted');
    }

    
    private function shouldShowRegistrationBonusNotice($user)
    {
        $hasFundedBefore = $user->wallet->transactions()
        ->where('transaction_action', WalletTransactionAction::WalletFunded->value)
        ->exists();
        
        if ($hasFundedBefore) {
            return false;
        }
        $registrationBonusService = new RegistrationBonusService;

        if(!$hasFundedBefore &&  !is_null($registrationBonusService->inactiveRegistrationBonus($user) )){
            return true;
        }
        return false;
    }
}
