<?php

namespace App\Http\Controllers;

use App\Models\StakingOdd;
use Illuminate\Http\Request;

class GetStakingOddsController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $odds = StakingOdd::active()->orderBy('score', 'DESC')->get();
        return $this->sendResponse($odds, 'staking odds fetched');
    }
}
