<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FcmRespository
{
    public function getActiveUsersDeviceTokens(Carbon $date)
    {
        $deviceTokens = DB::select('SELECT DISTINCT device_token
        FROM fcm_push_subscriptions AS fcm
        JOIN users ON fcm.user_id = users.id
        WHERE fcm.valid = ? AND users.last_activity_time >= ?', [1, $date]);
        return $deviceTokens;
    }
}