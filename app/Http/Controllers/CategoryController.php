<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Question;
use App\Models\GameType;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    //
    public function get($gameTypeId){ 
        $gameType = GameType::find($gameTypeId);

        if($gameType==null){
            return $this->sendResponse("Game type does not exist", "Game type does not exist");
        }
        if( config('trivia.product_launch.is_launching')){
            $categories = [];
            $launchCategories = config('trivia.product_launch.categories');
            foreach ($launchCategories as $category){
                
                $cat =Category::where('name',$category)->has('questions', '>', 0)->first();
                
                if($cat !== null){
                    $categories[] = $cat;
                }
            }
            return $this->sendResponse($categories, "All categories");
        }

        $categories = Category::all();
        $data = [];
        $cat=[];
            foreach($categories as $category){
                $questions = Question::where('category_id',$category->id)
                        ->where('game_type_id',$gameTypeId)->first();
                
                $data[] = $questions->category->name;
            }
            foreach($data as $data){
                $cat[] = Category::where('name',$data)->first();
            }
        return $this->sendResponse($cat, "All categories");
    }

    public function subCategories($catId, $gameTypeId){ 
        $cat = Category::find($catId);
        $gameType = GameType::find($gameTypeId);

        if ($cat==null || $gameType==null ){
            return $this->sendResponse("This Category or Gametype does not exist", "This Category or Gametype does not exist");
        }
        $subCategories = Category::where('category_id',$catId)->get();
        $data = [];
        $subCat = [];

        foreach($subCategories as $sub){
            $questions = Question::where('category_id',$sub->id)
                    ->where('game_type_id',$gameTypeId)->first();
            
            $data[] = $questions->category->name;
        }
        foreach($data as $data){
            $subCat[] = Category::where('name',$data)->first();
        }
        return $this->sendResponse($subCat, " Subcategories");
    }

}
