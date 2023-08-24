<?php

namespace App\Http\Controllers;

use App\Actions\UserCategories\AddCategoriesAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddUserCategoryController extends BaseController
{
    public function __invoke(Request $request, AddCategoriesAction $addCategoriesAction)
    {
        $user = auth()->user();

        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
        ]);

        $data = [];
        foreach ($request->data as $item) {
            $data[] = [
                'user_id' => $user->id,
                'category_id' => $item,
            ];
        }
    
        If(count($data) > 0){
            $addCategoriesAction->execute($data);
            return $this->sendResponse('Categories updated', 'Categories updated');
        }
    }
}
