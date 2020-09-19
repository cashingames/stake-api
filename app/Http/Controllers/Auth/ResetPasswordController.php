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

    public function reset(Request $request){
        //validate input:
        $data = $request->validate([
            'email' => ['required', "string", "email"],
            'password' => ['required', 'string'],                
            ]);
            
            
            
            if($data ){                   

                $user = User::where('email', $data['email'])->first();
                    
                    if (!$user){
                        return $this->sendError("email is incorrect", "email is incorrect");
                    }
                $user->password = $data['password'];

                $user->update(['password' => $data['password']]);

                return $this->sendResponse("Password reset successful.", "Password reset successful.");

            }else{

                return $this->sendError("password reset failed", "password reset failed");
            
            }
            

        }

}
