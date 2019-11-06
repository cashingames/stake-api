<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends BaseController
{
        /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    //
    public function me(){
        return $this->sendResponse(auth()->user()->profile(), "Current user profile");
    }
}
