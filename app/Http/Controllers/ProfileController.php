<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class ProfileController extends BaseController
{
    //
    public function me()
    {
        return $this->sendResponse(auth()->user()->profile, "Current user profile");
    }

    public function checkEmail(Request $request)
    {
        return Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        ])->validate();
    }

    public function edit(Request $request){

        $data = $request->validate([
            'gender' => ['nullable', 'string', 'max:20'],
            'dateOfBirth' => ['nullable', 'date'],
            'address' =>['nullable', 'string', 'between:10,300'],
            'state' => ['nullable', 'string', 'max:100'],
            'avatar' => ['nullable'],
            'accountName' => ['nullable', 'string', 'max:255'],
            'bankName' => ['nullable', 'string', 'max:255'],
            'accountNumber'=> ['nullable', 'string', 'max:255'],
            'currency' =>['nullable', 'string', 'max:100'],
            ]);


        $profile = auth()->user()->profile;
        
        if($profile == null){
            return $this->sendError(['Profile not found'], "Unable to update profile");
        }

        $profile->gender =  $data['gender'];
        $profile->date_of_birth = new Carbon($data['dateOfBirth']);
        $profile->address = $data['address'];
        $profile->state = $data['state'];
        $profile->avatar = $data['avatar'];
        $profile->account_name =$data['accountName'];
        $profile->bank_name = $data['bankName'];
        $profile->account_number = $data['accountNumber'];
        $profile->currency = $data['currency'];
        $profile->save();

        return $this->sendResponse($profile, "Profile Updated.");           
    }
}
