<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;
use App\Models\UserPoint;
use App\Models\User;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\isNull;

class BaseController extends Controller
{
    public $token;
    public $user;

    function __construct()
    {
        $this->user = auth()->user();
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        Log::info("Response sent to Frontend for  "
            . is_null($this->user) ? " undefined " : $this->user->username . " from endpoint  "
            .  url()->current() . " response " . json_encode($result));


        return response()->json($response, 200);
    }

    public function sendError($errors, $message)
    {
        $response = [
            'success' => false,
            'errors'    => $errors,
            'message' => $message,
        ];

        Log::info("Response sent to Frontend for  "
            . is_null($this->user) ? " undefined " : $this->user->username . " from endpoint  "
            . url()->current() . " response " . json_encode($errors));


        return response()->json($response, 400);
    }

    public function creditPoints($userId, $points, $description)
    {

        //create point traffic log
        UserPoint::create([
            'user_id' => $userId,
            'value' => $points,
            'description' => $description,
            'point_flow_type' => 'POINTS_ADDED'
        ]);
    }

    public function subtractPoints($userId, $points, $description)
    {

        //create point traffic log
        UserPoint::create([
            'user_id' => $userId,
            'value' => $points,
            'description' => $description,
            'point_flow_type' => 'POINTS_SUBTRACTED'
        ]);
    }
}
