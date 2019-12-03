<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;

class BaseController extends Controller
{
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

    public function SendError($errors, $message){
        $response = [
            'success' => false,
            'errors'    => $errors,
            'message' => $message,
        ];
        // {
        //     "message": "The given data was invalid.",
        //     "errors": {
        //         "plan_id": [
        //             "The plan id field is required."
        //         ]
        //     }
        // }

        return response()->json($response, 400);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    // public function sendError($error, $errorMessages = [], $code = 404)
    // {
    // 	$response = [
    //         'success' => false,
    //         'message' => $error,
    //     ];


    //     if(!empty($errorMessages)){
    //         $response['data'] = $errorMessages;
    //     }


    //     return response()->json($response, $code);
    // }
}
