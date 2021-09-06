<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Question;
use App\Models\GameType;
use App\Models\GameSession;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    //
    private $categories = [];

    private function getLaunchCategories(){
        if( config('trivia.product_launch.is_launching')){
            $launchCategories = config('trivia.product_launch.categories');
            foreach ($launchCategories as $category){
                
                $cat =Category::where('name',$category)->first();
                
                if($cat !== null){
                    $this->categories[] = $cat;
                }
            }
            return true;
        }
        return false;
    }

    public function get(){
        $launchCategories = $this->getLaunchCategories();
        if ($launchCategories){
            return $this->sendResponse($this->categories, "All categories");
        }
       
        return $this->sendResponse(Category::all(), "All categories");
    }

    public function getGameTypeCategories($gameTypeId){ 
        $gameType = GameType::find($gameTypeId);

        if($gameType==null){
            return $this->sendResponse("Game type does not exist", "Game type does not exist");
        }
        
        $launchCategories = $this->getLaunchCategories();
        if ($launchCategories){
            return $this->sendResponse($this->categories, "All categories");
        }

        $allCategories = Category::all();
        $data = [];
        $cat=[];
            foreach($allCategories as $category){
                $questions = Question::where('category_id',$category->id)
                        ->where('game_type_id',$gameTypeId)->first();
                
                if($questions!==null){
                    $data[] = $questions->category->name;
                }
                
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
        $subCategories = Category::where('category_id',$catId)->has('questions', '>', 0)->get();
        $data = [];
        $subCat = [];

        foreach($subCategories as $sub){
            $questions = Question::where('category_id',$sub->id)
                    ->where('game_type_id',$gameTypeId)->first();
            if($questions !==null){
                $data[] = $questions->category->name;
            }
           
        }
        foreach($data as $data){
            $subCat[] = Category::where('name',$data)->first();
        }
        return $this->sendResponse($subCat, " Subcategories");
    }

    public function timesPlayed($catId){
        $category = Category::find($catId);
        if($category === null){
            return $this->sendError("Invalid Category", " Invalid Category");
        }

        $hasSubCategory = Category::where('category_id',$category->id)->get();
       
        if(count($hasSubCategory)==0){
            $countAsUser = GameSession::where('category_id',$category->id)->where('user_id',$this->user->id)->count();
            $countAsOpponent = GameSession::where('category_id',$category->id)->where('opponent_id',$this->user->id)->count();

            return $this->sendResponse($countAsUser + $countAsOpponent, " times played");
        }
         
        $subPlayedCount = [];
        foreach($hasSubCategory as $sub){
            $countAsUser = GameSession::where('category_id',$sub->id)->where('user_id',$this->user->id)->count();
            $countAsOpponent = GameSession::where('category_id',$sub->id)->where('opponent_id',$this->user->id)->count();
            
            $subPlayedCount[] = $countAsUser + $countAsOpponent;
        }
        return $this->sendResponse(array_sum($subPlayedCount), " times played");
    }

}
