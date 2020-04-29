<?php

namespace App\Http\Controllers;

use App\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $user = $this->user->load('profile');
        $result = [
            'user' => $user,
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

    public function logError($data){
        Log::error($data);
        return "";
    }
}
