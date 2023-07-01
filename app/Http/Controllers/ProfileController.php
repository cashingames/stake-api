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
use App\Enums\ClientPlatform;
use Illuminate\Support\Facades\Event;
use App\Events\AchievementBadgeEvent;
use App\Enums\AchievementType;

class ProfileController extends BaseController
{

    public function editPIForGameArk(Request $request)
    {
        $data = $request->validate([
            'username' => [
                'required', 'string', 'max:20',
                Rule::unique('users', 'username')->ignore($this->user->id),
            ],
            'email' => [
                'required', 'string', 'max:255', 'email',
                Rule::unique('users', 'email')->ignore($this->user->id),
            ],
            'gender' => ['nullable', 'string', 'max:20'],
            'dateOfBirth' => ['nullable', 'date'],
        ]);

        $profile = $this->user->profile;

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
        
    }


    public function editPersonalInformation(Request $request)
    {
        $this->editPIForGameArk($request);
        return $this->sendResponse(true, "Profile Updated.");
    }

    public function updateReferrer(Request $request)
    {

        $data = $request->validate([
            'referrer' => ['required', 'string', 'exists:users,username']
        ]);
        $user = auth()->user();
        $profile = $user->profile;

        if ($profile == null) {
            return $this->sendError(['Profile not found'], "Unable to update profile");
        }

        $profile->referrer = $data['referrer'];
        $profile->save();

        $profileReferral = User::where('username', $data["referrer"])->first();
        if ($profileReferral != null) {
            Event::dispatch(new AchievementBadgeEvent($profileReferral, AchievementType::REFERRAL, null));
        }

        return $this->sendResponse(true, "Profile Updated.");
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
