<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\BubbleBlitzGameModesResponse;
use App\Models\Game;
use App\Models\GameMode;
use Illuminate\Http\Request;

class BubbleBlitzGameModesController extends BaseController
{
    public function __invoke(Game $games)
    {
        // $gameModes = GameMode::where('game_id', 2)->get();
        $gameModes = $games->gameModes()->where('name', 'Bubble Blitz')->get();
        $data = [];
        $response = new BubbleBlitzGameModesResponse();
        foreach($gameModes as $gameMode){
            $data[] = $response->transform($gameMode);
        }
        return $this->sendResponse($data, 'GameModes');
    }}
