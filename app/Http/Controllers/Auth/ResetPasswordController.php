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

    public function reset(Request $request, $email){
        //validate input:
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],                
            ]);
            
            if($data ){                   

                $user = User::where('email', $email)->first();
                    
                if (!$user){
                    return $this->sendError("email is incorrect", "email is incorrect");
                }
                $user->password = bcrypt($data['password']);
                $user->save();
                auth()->attempt(['email','password']);
                
                return $this->sendResponse($user, "Password reset successful.");
            }else{
                return $this->sendError("password reset failed", "password reset failed");
            
            }
            

        }

}
