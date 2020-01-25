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
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return $this->sendResponse(auth()->user(), 'User details');
    }

    public function plans()
    {
        $myPlans = auth()->user()->plans()->wherePivot('is_active', true)->get();
        return $this->sendResponse($myPlans, 'User active plans');
    }
}
