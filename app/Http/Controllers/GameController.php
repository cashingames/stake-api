<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mode;
use App\Models\GameType;
use App\Models\Boost;
use App\Models\Achievement;

class GameController extends BaseController
{
    //

    public function modes(){
        return $this->sendResponse(Mode::all(), "Game Modes");
    }

    public function gameTypes(){
        return $this->sendResponse(GameType::all(), "Game Types");
    }

    public function boosts(){
        return $this->sendResponse(Boost::all(), "Game Boosts");
    }

    public function achievements(){
        return $this->sendResponse(Achievement::all(), "Achievements");
    }

}
