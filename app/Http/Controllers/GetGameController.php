<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\GetGamesResponse;
use App\Models\Game;
use Illuminate\Http\Request;

class GetGameController extends BaseController
{
    public function __invoke()
    {
        $games = Game::all();
        $data = [];
        $response = new GetGamesResponse();
        foreach($games as $game){
            $data[] = $response->transform($game);
        }
        return $this->sendResponse($data, 'Games');
    }
}
