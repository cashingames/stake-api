<?php

namespace App\Http\Controllers;

use App\Plan;
use Illuminate\Http\Request;

class PlanController extends BaseController
{
    //
    public function get(){
        return $this->sendResponse(Plan::all(), "General plan list");
    }
}
