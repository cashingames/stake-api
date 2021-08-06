<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mode;

class GameController extends BaseController
{
    //

    public function modes(){
        return $this->sendResponse(Mode::all(), "Game Modes");
    }

}
