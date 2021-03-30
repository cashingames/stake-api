<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    //
    public function get(){ 
        
        if( config('trivia.campaign.enabled')){
            $categories = [];
            $campaignCategories = config('trivia.campaign.categories');
            foreach ($campaignCategories as $category){
                $categories[] = Category::where('name',$category)->has('questions', '>', 0)->first();
            }
            return $this->sendResponse($categories, "All campaign categories");
        }

        $categories = Category::has('questions', '>', 0)->get();
        return $this->sendResponse($categories, "All categories");
    }
}
