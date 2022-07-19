<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UpdateEmailVerifiedController extends BaseController
{
    public function __invoke($email)
    {   
        User::where('email',$email)->update(['email_verified_at' => now()]);

        return view('redirectToEmailVerified');
    }
}
