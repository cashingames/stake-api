<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $recentLiveTrivia = DB::select(
            "select * from trivias order by created_at desc
            limit 10"
        );

        $response = [];
       
        foreach($recentLiveTrivia as $liveTrivia){
            $response[]= (new LiveTriviaStatusResponse())->returnAsObject($liveTrivia);
        }
        return $response;
    }

}
