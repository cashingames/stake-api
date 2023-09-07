<?php

namespace App\Http\Controllers;

use App\Actions\UserCategories\GetUserCategoriesAction;
use App\Http\ResponseHelpers\GetUserCategoriesResponse;

class GetUserCategoriesController extends BaseController
{
    public function __invoke(GetUserCategoriesAction $getUserCategoriesAction)
    {
        $user = auth()->user();
        $data = [];
        $response = new GetUserCategoriesResponse;
        $userCategories = $getUserCategoriesAction->execute($user);
        foreach($userCategories as $userCategory){
            $data[] = $response->getUserCategories($userCategory);
        }
        return $this->sendResponse($data, 'User Categories gotten successfully');
    }
}