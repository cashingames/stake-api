<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends BaseController
{


    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth:api');
    // }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = $this->user;
        $result = [
            'user' => $user,
            'profile' => $user->profile,
            'plans' => $user->activePlans()->get(),
            'wallet' => $user->wallet
        ];
        return $this->sendResponse($result, 'User details');
    }

    public function plans()
    {
        $myPlans = $this->user->activePlans()->get();
        return $this->sendResponse($myPlans, 'User active plans');
    }
}
