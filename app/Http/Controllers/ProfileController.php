<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use App\User;
use App\Profile;

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
            'firstName' =>['required', 'string', 'max:20'],
            'lastName' =>['required', 'string', 'max:20'],
            'username'=>['required', 'string', 'max:20'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:150'],
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


        $user = auth()->user();
        $profile = $user->profile;        
        
           if($profile == null){
                return $this->sendError(['Profile not found'], "Unable to update profile");
            }

           
                $profile->first_name= $data['firstName'];
                $profile->last_name= $data['lastName'];
                $profile->gender =  $data['gender'];
                $profile->date_of_birth = new Carbon($data['dateOfBirth']);
                $profile->address = $data['address'];
                $profile->state = $data['state'];   
                $profile->avatar = $data['avatar'] ;            
                $profile->account_name =$data['accountName'];
                $profile->bank_name = $data['bankName'];
                $profile->account_number = $data['accountNumber'];
                $profile->currency = $data['currency'];
                $profile->save();

                $user->update(['username'=> $data['username'],
                                'phone' => $data['phone'],
                                'email' => $data['email'],
                            ]);
           

            return $this->sendResponse($user, "Profile Updated.");
 
    }


    public function addProfilePic(Request $request)
    {
            // try{

                $data  = $request->validate([
                    'avatar'     =>  'required|image|mimes:jpeg,png,jpg,gif,base64|max:2048'
                ]);

                if(!$data){
                    return $this->sendError("The file must be an image", "The file must be an image");
                }

                $user = auth()->user();
                $profile = $user->profile;   

                if ($request->hasFile('avatar')) {
                $image = $request->file('avatar');
                $name = $image->getClientOriginalName();
                $destinationPath = public_path('/uploads');
                $image->move($destinationPath, $name);

                }

                $profile->avatar = $name;
                $profile->save();

                return $this->sendResponse($profile, "Profile Updated.");
            // }
            // catch(\Exception $e){
            //     return $this->sendError("Profile Picture Not Saved", 'Profile Picture Not Saved');
            // }


    }


}
