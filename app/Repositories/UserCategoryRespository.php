<?php

namespace App\Repositories;

use App\Models\UserCategory;
use Illuminate\Support\Facades\DB;

class UserCategoryRespository
{
    public function addCategory($data)
    {
        DB::table('user_categories')->insert($data); 
    }

    public function removeCategory($id)
    {
        UserCategory::where('category_id', $id)->delete();
    }

    public function getUserCategories($user){
        return $user->userCategories();
    }
}