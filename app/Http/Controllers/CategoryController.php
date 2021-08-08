<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    //
    public function get(){ 
        
        $categories = Category::where('category_id',null)->get();
        return $this->sendResponse($categories, "All categories");
    }
}
