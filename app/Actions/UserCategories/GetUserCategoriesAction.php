<?php
namespace App\Actions\UserCategories;

use App\Repositories\UserCategoryRespository;

class GetUserCategoriesAction
{ 
    public function __construct(
        private readonly UserCategoryRespository $userCategoryRespository,
    ) {
    }

    public function execute($user) {
        $userCategories = $this->userCategoryRespository->getUserCategories($user);
        return $userCategories;
    }
}