<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Profile;

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

    public function editPersonalInformation(Request $request)
    {

        $data = $request->validate([
            'firstName' => ['required', 'string', 'max:20'],
            'lastName' => ['required', 'string', 'max:20'],
            'username' => ['required', 'string', 'max:20'],
            'phoneNumber'=> ['nullable', 'string', 'max:11'],
            'email' => ['required', 'email'],
            'password' =>['nullable','string'],
            'gender' => ['nullable', 'string', 'max:20'],
            'dateOfBirth' => ['nullable', 'date'],
        ]);


        $user = auth()->user();
        $profile = $user->profile;

        if ($profile == null) {
            return $this->sendError(['Profile not found'], "Unable to update profile");
        }

        $user->username = $data['username'];
        
        if(isset($data['phoneNumber']) &&  !is_null($data['phoneNumber'])){
            $user->phone_number = $data['phoneNumber'];
        }
        if(isset($data['password']) &&  !is_null($data['password'])){
            $user->password = bcrypt($data['password']);
        }
       
        $profile->first_name = $data['firstName'];
        $profile->last_name = $data['lastName'];

        if(isset($data['gender']) &&  !is_null($data['gender'])){
            $profile->gender =  $data['gender'];
        }

        if(isset($data['dateOfBirth']) && !is_null($data['dateOfBirth'])){
            $profile->date_of_birth = (new Carbon($data['dateOfBirth']))->toDateString() ;
        }

        $user->save();
        $profile->save();

        return $this->sendResponse($user, "Profile Updated.");
    }

    public function editBank(Request $request)
    {

        $data = $request->validate([
            'accountName' => ['required', 'string', 'max:255'],
            'bankName' => ['required', 'string', 'max:255'],
            'accountNumber' => ['required', 'string', 'max:255'],
        ]);


        $user = auth()->user();
        $profile = $user->profile;

        if ($profile == null) {
            return $this->sendError(['Profile not found'], "Unable to update profile");
        }

        $profile->account_name = $data['accountName'];
        $profile->bank_name = $data['bankName'];
        $profile->account_number = $data['accountNumber'];
        $profile->save();

        return $this->sendResponse($user, "Profile Updated.");
    }

    public function addProfilePic(Request $request)
    {
        // try{

        $data  = $request->validate([
            'avatar'     =>  'required|image|mimes:jpeg,png,jpg,gif,base64|max:2048'
        ]);

        if (!$data) {
            return $this->sendError("The file must be an image", "The file must be an image");
        }

        $profile = $this->user->profile;

        if ($request->hasFile('avatar')) {
            $image = $request->file('avatar');
            $name = $this->user->id.".".$image->guessExtension();
            $destinationPath = public_path('avatar');
            $profile->avatar = $name;
            $image->move($destinationPath, $name);
            $profile->save();
        }

        return $this->sendResponse($profile, "Profile Updated.");
        // }
        // catch(\Exception $e){
        //     return $this->sendError("Profile Picture Not Saved", 'Profile Picture Not Saved');
        // }


    }
}
