<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LiveTrivia;
use App\Http\ResponseHelpers\LiveTriviaStatusResponse;


class GetRecentLiveTriviaController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {   
        $recentLiveTrivia = LiveTrivia::recent()->limit(10)->get();

        $response = [];
       
        foreach($recentLiveTrivia as $liveTrivia){
            $response[]= (new LiveTriviaStatusResponse())->returnAsObject($liveTrivia);
        }
        return $response;
    }

}
