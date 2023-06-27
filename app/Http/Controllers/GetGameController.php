<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class GetGameController extends BaseController
{
    public function __invoke()
    {
        $games = Game::all();

        return $this->sendResponse($games, 'Games');
    }
}
