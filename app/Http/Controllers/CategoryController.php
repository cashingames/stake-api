<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    //
    public function get(){ 

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
        
        $categories = Category::where('category_id',null)->has('questions', '>', 0)->get();
        return $this->sendResponse($categories, "All categories");
    }

    public function subCategories($id){ 
        $cat = Category::find($id);
        if ($cat==null){
            return $this->sendResponse("This Category does not exist", "This Category does not exist");
        }
        $subCategories = Category::where('category_id' ,$cat->id)->has('questions', '>', 0)->get();
        return $this->sendResponse($subCategories, "$cat->name"." Subcategories");
    }

    public function allGames(){
        if( config('trivia.product_launch.is_launching')){
            $categories = [];
            $launchCategories = config('trivia.product_launch.categories');
            foreach ($launchCategories as $category){
                
                $cat =Category::where('name',$category)->has('questions', '>', 0)->first();
                if($cat !== null){
                    $categories[] = $cat;
                }
            }
            $games =[];
            foreach($categories as $c){
               
                $cat = Category::where('category_id', $c->id)->has('questions', '>', 0)->get();
                if($cat !== null){
                    $games[] = $cat;
                }
               
            }
           
            return $this->sendResponse($games, "All games");
        }
        $games = Category::where('category_id','!=', null)->has('questions', '>', 0)->get();
        return $this->sendResponse($games, "all games");
    }

}
