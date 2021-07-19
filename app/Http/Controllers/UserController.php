<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends BaseController
{
    //

    public function me()
    {
        $user = $this->user->load('profile');
        $result = [
            'user' => $user,
            'wallet' => $user->wallet
        ];
        return $this->sendResponse($result, 'User details');
    }
}
