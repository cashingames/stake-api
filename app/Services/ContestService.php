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
        $contests = Contest::select(
            'id',
            'name',
            'description',
            'display_name as displayName',
            'start_date as startDate',
            'end_date as endDate',
            'contest_type as contestType',
            'entry_mode as entryMode'
        )->get();

        return  $contests;
    }

    public function getSingleContest($id)
    {
        $contest = Contest::select(
            'id',
            'name',
            'description',
            'display_name as displayName',
            'start_date as startDate',
            'end_date as endDate',
            'contest_type as contestType',
            'entry_mode as entryMode'
        )->where('id', $id)->first();

        return $contest;
    } 
}
