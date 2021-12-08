<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function reset(Request $request, $email, $code){
       
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],                
            ]);
            
        $validRecord = DB::table('password_resets')->where('email', $email)->where('token', $code)->first();
                
        if ($validRecord == null){
            return $this->sendError("email or token is incorrect", "email or token is incorrect");
        }

        $user = User::where('email', $email)->first();
            
        $user->password = bcrypt($data['password']);
        $user->save();
            
        return $this->sendResponse($user, "Password reset successful.");
          

    }

}
