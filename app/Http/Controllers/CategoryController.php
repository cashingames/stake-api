<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    //
    public function get(){
        $categories = Category::has('questions', '>', 0)->get();
        return $this->sendResponse($categories, "All categories");
    }
}
