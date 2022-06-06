<?php

namespace App\Http\Controllers;

use App\Models\LiveTrivia;
use App\Traits\Utils\DateUtils;
use Illuminate\Http\Request;

class LiveTriviaStatusController extends BaseController
{
    use DateUtils;
    /**
     * Single action playground
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $liveTrivia = LiveTrivia::active()->first(); //@TODO: return playedStatus for users that have played and status 
        return $liveTrivia;
    }
}
