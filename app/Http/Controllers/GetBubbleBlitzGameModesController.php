<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\BubbleBlitzGameModesResponse;
use App\Models\Game;
use App\Models\GameMode;
use Illuminate\Http\Request;

class GetBubbleBlitzGameModesController extends BaseController
{
    public function __invoke()
    {
        $game = Game::where('name', 'Bubble Blitz')->first()->id;
        $gameModes = GameMode::where('game_id', $game)->get();
        $data = [];
        $response = new BubbleBlitzGameModesResponse();
        foreach($gameModes as $gameMode){
            $data[] = $response->transform($gameMode);
        }
        return $this->sendResponse($data, 'GameModes');
    }}
