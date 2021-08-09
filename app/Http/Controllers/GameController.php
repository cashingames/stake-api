<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mode;
use App\Models\GameType;

class GameController extends BaseController
{
    //

    public function modes(){
        return $this->sendResponse(Mode::all(), "Game Modes");
    }

    public function gameTypes(){
        return $this->sendResponse(GameType::all(), "Game Types");
    }


}
