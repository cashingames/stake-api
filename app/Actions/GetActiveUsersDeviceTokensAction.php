<?php
namespace App\Actions;

use App\Repositories\FcmRespository;
use Carbon\Carbon;

class GetActiveUsersDeviceTokensAction
{

    public function __construct(
        private readonly FcmRespository $fcmRespository,
    ) {
    }

    public function execute(Carbon $date) {
        $usersDeviceTokens = $this->fcmRespository->getActiveUsersDeviceTokens($date);
        return $usersDeviceTokens;
    }
}