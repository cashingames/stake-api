<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use URL;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends BaseController
{
    public function editPersonalInformation(Request $request)
    {

        $data = $request->validate([
            'firstName' => ['required', 'string', 'max:20'],
            'lastName' => ['required', 'string', 'max:20'],
            'username' => [
                'required', 'string', 'max:20',
                Rule::unique('users','username')->ignore($this->user->id),
            ],
            'email' => [
                'required', 'string', 'max:255','email',
                Rule::unique('users','email')->ignore($this->user->id),
            ],
           
            // 'phoneNumber' => [
            //     'required','min:11','max:11',
            //     Rule::unique('users','phone_number')->ignore($this->user->id),
            // ],
            'gender' => ['nullable', 'string', 'max:20'],
            'dateOfBirth' => ['nullable', 'date'],
        ]);

        // if (isset($data['phoneNumber']) &&  !is_null($data['phoneNumber'])) { 
        //     $this->user->phone_number = $data['phoneNumber'];
        //     $this->user->save();
        // }
        $profile = $this->user->profile;
        $profile->first_name = $data['firstName'];
        $profile->last_name = $data['lastName'];

        $this->user->username = $data['username'];
        $this->user->email = $data['email'];
        $this->user->save();

        if (isset($data['gender']) &&  !is_null($data['gender'])) {
            $profile->gender =  $data['gender'];
        }

        if (isset($data['dateOfBirth']) && !is_null($data['dateOfBirth'])) {
            $profile->date_of_birth = (new Carbon($data['dateOfBirth']))->toDateString();
        }
        $profile->save();

        return $this->sendResponse($this->user, "Profile Updated.");
    }

    public function editBank(Request $request)
    {

        $data = $request->validate([
            'accountName' => ['required', 'string', 'max:255'],
            'bankName' => ['required', 'string', 'max:255'],
            'accountNumber' => [
                'required', 'string', 'max:255',
                Rule::unique('profiles', 'account_number')->ignore($this->user->id),
            ],
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

        // $data  = $request->validate([
        //     'avatar'     =>  'required|image|mimes:jpeg,png,jpg,gif,base64|max:2048'
        // ]);

        // if (!$data) {
        //     return $this->sendError("The file must be an image", "The file must be an image");
        // }

        $profile = $this->user->profile;

        if ($request->hasFile('avatar')) {
            $image = $request->file('avatar');
            $name = uniqid() . $this->user->username . "." . $image->guessExtension();
            $destinationPath = public_path('avatar');
            $profile->avatar = 'avatar/' . $name;
            $image->move($destinationPath, $name);
            // echo $destinationPath;

            $profile->save();

            return $this->sendResponse($profile, "Profile Updated.");
        }

        return $this->sendResponse("No file found for upload", "No file found for upload");
    }


    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($data['password'] === $data['new_password']) {
            return $this->sendError("The new password must be different from the old password.", "The new password must be different from the old password.");
        }

        if (Hash::check($data['password'], $this->user->password)) {
            $this->user->update(['password' => bcrypt($data['new_password'])]);
            return $this->sendResponse("Password Changed!.", "Password Changed!.");
        }
        return $this->sendError("Old password inputed does not match existing password.", "Old password inputed does not match existing password.");
    }
}
