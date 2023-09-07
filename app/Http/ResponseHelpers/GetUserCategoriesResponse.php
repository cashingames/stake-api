<?php

namespace App\Http\ResponseHelpers;

class GetUserCategoriesResponse
{
    public function getUserCategories($userCategory)
    {
        return [
            'name' => $userCategory->name,
            'icon' => $userCategory->icon,
            'game_id' => $userCategory->id,
            'description' => $userCategory->description,
        ];
    }
}