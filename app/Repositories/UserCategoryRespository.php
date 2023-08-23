<?php

namespace App\Repositories;

use App\Models\UserCategory;
use Illuminate\Support\Facades\DB;

class UserCategoryRespository
{
    public function addCategory($userId, $categoryId)
    {
        DB::table('user_categories')->insert([
            'user_id' => $userId,
            'category_id' => $categoryId,
        ]);   
    }

    public function removeCategory($id)
    {
        UserCategory::where('category_id', $id)->delete();
    }
}