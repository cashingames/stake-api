<?php
namespace App\Actions\UserCategories;

use App\Repositories\UserCategoryRespository;

class AddCategoriesAction
{ 
    public function __construct(
        private readonly UserCategoryRespository $userCategoryRespository,
    ) {
    }

    public function execute($userId, $categoryId) {
        $addCategory = $this->userCategoryRespository->addCategory($userId, $categoryId);
        return $addCategory;
    }
}