<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Contest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContestService
{

    public function getContests()
    {
        return Contest::select(
            'id',
            'name',
            'description',
            'display_name as displayName',
            'start_date as startDate',
            'end_date as endDate',
            'contest_type as contestType',
            'entry_mode as entryMode'
        )->limit(50)->get();
    }

    public function getSingleContest($id)
    {
        return  Contest::select(
            'id',
            'name',
            'description',
            'display_name as displayName',
            'start_date as startDate',
            'end_date as endDate',
            'contest_type as contestType',
            'entry_mode as entryMode'
        )->find($id);
    }
}
