<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\User;

class ResetPasswordController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    // use ResetsPasswords;

    public function reset(Request $request)
    {

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['required', 'string', 'min:8', 'max:15'],
            'code' => ['string', 'required']
        ]);


        $user = User::where('phone_number', $data['phone'])->first();

        if (!is_null($user)) {

            $user->password = bcrypt($data['password']);
            $user->save();

            return $this->sendResponse("Password reset successful.", 'Password reset successful');
        }
        return $this->sendError("Phone number does not exist", "Phone number does not exist");
    }
}