<?php

namespace App\Http\ResponseHelpers;

class GetGamesResponse
{
    public function transform($game)
    {
        return [
            'name' => $game->name,
            'icon' => $game->icon,
            'background_image' => $game->background_image,
            'is_enabled' => $game->is_enabled,
        ];
    }
}