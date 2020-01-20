<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    public function editProfile(Request $request){

        $data = $request->validate([
            'gender' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'address' =>['nullable', 'string', 'between:10,300'],
            'state' => ['nullable', 'string', 'max:100'],
            'avatar' => ['nullable'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_number'=> ['nullable', 'string', 'max:255'],
            'currency' =>['nullable', 'string', 'max:100'],
            ]);


         $profile = auth()->user()->profile()
                            ->update([
                                        'gender' => $data['gender'],
                                        'date_of_birth'=> $data['date_of_birth'],
                                         'address' => $data['address'],
                                        'state' => $data['state'],
                                        'avatar' => $data['avatar'],
                                        'account_name' => $data['account_name'],
                                        'bank_name'  => $data['bank_name' ],
                                        'account_number'  => $data['account_number'],
                                        'currency'  => $data['currency' ],
                                    ]);
            $getProfile = auth()->user()->profile()->get();
            if($profile){
                return $this->sendResponse($getProfile, "Profile Updated.");           
            }

            else{
                return response()->json('Unable to update profile', 400);
            }
            
       

    }
}
