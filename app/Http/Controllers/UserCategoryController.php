<?php

namespace App\Http\Controllers;

use App\Actions\UserCategories\AddCategoriesAction;
use App\Actions\UserCategories\RemoveCategoriesAction;
use App\Models\Category;
use App\Models\UserCategory;
use App\Repositories\UserCategoryRespository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserCategoryController extends BaseController
{
    public function addUserCategory(Request $request, AddCategoriesAction $addCategoriesAction)
    {
        $user = auth()->user();

        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
        ]);

       //Check if validation fails
        if ($validator->fails()) {
            return $this->sendError('Request must be an array', 'Request must be an array');
        }

        // Retrieve the data array from the validated input
        $data = $request->data;

        foreach ($data as $item) {
            $category = Category::where('name', $item)->first();
            if ($category) {
                $addCategoriesAction->execute($user->id, $category->id);
            } else {
            return $this->sendError('Invalid Category', 'Invalid category');
            }
        }
        return $this->sendResponse('Categories updated', 'Categories updated');
    }

    public function removeUserCategory(Request $request, RemoveCategoriesAction $removeCategoriesAction)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
        ]);
        $data = $request->data;

        foreach ($data as $item) {
            $category = Category::where('name', $item)->first();
            if($category){
                $removeCategoriesAction->execute($category->id);
            }
        }
    }
}
