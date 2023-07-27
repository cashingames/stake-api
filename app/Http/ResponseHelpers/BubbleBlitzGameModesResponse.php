<?php

namespace App\Http\ResponseHelpers;

class BubbleBlitzGameModesResponse
{
    public function transform($gameMode)
    {
        return [
            'name' => $gameMode->name,
            'description' => $gameMode->description,
            'display_name' => $gameMode->display_name,
        ];
    }
}
 