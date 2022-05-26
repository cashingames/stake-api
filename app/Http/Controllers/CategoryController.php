<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Question;
use App\Models\GameType;
use App\Models\GameSession;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{

    // public function timesPlayed($catId)
    // {
    //     $category = Category::find($catId);
    //     if ($category === null) {
    //         return $this->sendError("Invalid Category", " Invalid Category");
    //     }

    //     $hasSubCategory = Category::where('category_id', $category->id)->get();

    //     if (count($hasSubCategory) == 0) {
    //         $countAsUser = GameSession::where('category_id', $category->id)->where('user_id', $this->user->id)->count();
    //         $countAsOpponent = GameSession::where('category_id', $category->id)->where('opponent_id', $this->user->id)->count();

    //         return $this->sendResponse($countAsUser + $countAsOpponent, " times played");
    //     }

    //     $subPlayedCount = [];
    //     foreach ($hasSubCategory as $sub) {
    //         $countAsUser = GameSession::where('category_id', $sub->id)->where('user_id', $this->user->id)->count();
    //         $countAsOpponent = GameSession::where('category_id', $sub->id)->where('opponent_id', $this->user->id)->count();

    //         $subPlayedCount[] = $countAsUser + $countAsOpponent;
    //     }
    //     return $this->sendResponse(array_sum($subPlayedCount), " times played");
    // }
}
