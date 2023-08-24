<?php
namespace App\Actions\UserCategories;

use App\Repositories\UserCategoryRespository;

class AddCategoriesAction
{ 
    public function __construct(
        private readonly UserCategoryRespository $userCategoryRespository,
    ) {
    }

    public function execute($data) {
        return $this->userCategoryRespository->addCategory($data);
    }
}