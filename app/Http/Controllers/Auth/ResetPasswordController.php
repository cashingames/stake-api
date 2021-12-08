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

    public function reset(Request $request){
       
        $data = $request->validate([
                'password' => ['required', 'string', 'min:8', 'confirmed'], 
                'email' => ['email', 'required'] ,
                'code' => ['string','required']              
            ]);
            
        $validRecord = DB::table('password_resets')->where('email', $data['email'])->where('token', $data['code'])->first();
                
        if ($validRecord == null){
            return $this->sendError("email or token is incorrect", "email or token is incorrect");
        }

        $user = User::where('email', $data['email'])->first();
            
        $user->password = bcrypt($data['password']);
        $user->save();
            
        return $this->sendResponse($user, "Password reset successful.");
          

    }

}
