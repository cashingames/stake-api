<?php
namespace App\Actions\UserCategories;

use App\Repositories\UserCategoryRespository;

class RemoveCategoriesAction
{ 
    public function __construct(
        private readonly UserCategoryRespository $userCategoryRespository,
    ) {
    }

    public function execute($id) {
        $removeCategory = $this->userCategoryRespository->removeCategory($id);
        return $removeCategory;
    }
}