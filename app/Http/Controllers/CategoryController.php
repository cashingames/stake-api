<?php

namespace App\Http\Controllers;

use App\Triva\Model\Category;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    //
    public function get(){
        $categories = Category::all();
        return $this->sendResponse($categories, "All categories");
    }
}
