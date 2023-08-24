<?php

namespace App\Http\Controllers;

use App\Actions\UserCategories\RemoveCategoriesAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RemoveUserCategoryController extends BaseController
{
    public function __invoke(Request $request, RemoveCategoriesAction $removeCategoriesAction)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
        ]);
        $data = [];
        foreach ($request->data as $item) {
            $data[] = [
                'category_id' => $item,
            ];
        }
        if (count($data) > 0) {
            $removeCategoriesAction->execute($data);
        }
    }
}
