<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Question;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    //
    public function get($gameTypeId){ 

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

    public function subCategories($id){ 
        $cat = Category::find($id);
        if ($cat==null){
            return $this->sendResponse("This Category does not exist", "This Category does not exist");
        }
        $subCategories = Category::where('category_id' ,$cat->id)->has('questions', '>', 0)->get();
        return $this->sendResponse($subCategories, "$cat->name"." Subcategories");
    }

}
