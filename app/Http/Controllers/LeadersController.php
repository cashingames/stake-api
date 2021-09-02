<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class LeadersController extends BaseController
{
    //

    public function globalLeaders(){

        $leaders = User::orderBy('points', 'desc')->with('profile')->limit(50)->get();

        return $this->sendResponse($leaders, "Leaders");
    }


}
