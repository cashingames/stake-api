<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends BaseController
{
    //
    public function me(){
        return $this->sendResponse(auth()->user()->profile(), "Current user profile");
    }

    public function checkEmail(Request $request){
        return Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        ])->validate();
    }
}
