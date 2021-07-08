<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;

class BaseController extends Controller
{
    public $token;
    public $user;

    function __construct (){
        $this->user = auth()->user();
    }

     /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message)
    {
    	$response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];


        return response()->json($response, 200);
    }

    public function sendError($errors, $message){
        $response = [
            'success' => false,
            'errors'    => $errors,
            'message' => $message,
        ];

        return response()->json($response, 400);
    }

}
